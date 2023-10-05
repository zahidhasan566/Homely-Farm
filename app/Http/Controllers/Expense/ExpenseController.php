<?php

namespace App\Http\Controllers\Expense;

use App\Http\Controllers\Controller;
use App\Models\CategoryLocation;
use App\Models\Employee;
use App\Models\ExpenseDetails;
use App\Models\ExpenseHead;
use App\Models\ExpenseMaster;
use App\Models\Items;
use App\Models\ItemsCategory;
use App\Models\ProductionDetails;
use App\Models\ProductionMaster;
use App\Models\StockBatch;
use App\Traits\CodeGeneration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    use CodeGeneration;
        public function index(Request $request){
            $take = $request->take;
            $search = $request->search;
            $expense =  ExpenseMaster::leftJoin('ExpenseDetails','ExpenseDetails.ExpenseCode','ExpenseMaster.ExpenseCode')
                ->leftJoin('ExpenseHead','ExpenseHead.HeadCode','ExpenseMaster.HeadCode')
                ->leftJoin('ItemsCategory','ItemsCategory.CategoryCode','ExpenseMaster.CategoryCode')
                ->where(function ($q) use ($search) {
                $q->where('ExpenseMaster.ExpenseCode', 'like', '%' . $search . '%');
                $q->orWhere('ExpenseHead.HeadCode', 'like', '%' . $search . '%');
                $q->orWhere('ExpenseMaster.ExpenseDate', 'like', '%' . $search . '%');
            })
                //->orderBy('ExpenseMaster.ExpenseCode','desc')
                ->select(
                    'ExpenseMaster.ExpenseCode',
                    'ExpenseHead.ExpenseHead',
                    DB::raw("convert(varchar(10),ExpenseMaster.ExpenseDate,23) as ExpenseDate"),
                    'ItemsCategory.CategoryName',
                    'ExpenseMaster.Naration',
                    'ExpenseMaster.Amount',
                    DB::raw("convert(varchar(10),ExpenseMaster.PrepareDate,23) as PrepareDate"),

                )->groupBy(
                    'ExpenseMaster.ExpenseCode',
                    'ExpenseHead.ExpenseHead',
                    'ExpenseMaster.ExpenseDate',
                    'ItemsCategory.CategoryName',
                    'ExpenseMaster.Naration',
                    'ExpenseMaster.Amount',
                    'ExpenseMaster.PrepareDate',

                )
                ->paginate($take);
            return $expense;
        }
    public function getSupportingData(){
        return response()->json([
            'status' => 'success',
            'expenseHead'=> ExpenseHead::all(),
            'employee'=> Employee::all(),
            'category' => ItemsCategory::where('Active','Y')->get(),
            //'items' => Items::join('Items','Items.ItemCode','ItemsCategory')->where('ItemsCategory.Active','Y')->get(),
        ]);
    }
    public function getCategoryWiseItemData(Request $request){
        $location = CategoryLocation::select(
            'CategoryLocation.CategoryCode',
            'CategoryLocation.LocationCode',
            'CategoryLocation.Active',
            'Location.LocationName',
        )->join('Location','Location.LocationCode','CategoryLocation.LocationCode')
            ->where('CategoryLocation.CategoryCode',$request->CategoryCode)
            ->where('CategoryLocation.Active','Y')->get();

        return response()->json([
            'status' => 'success',
            'items' => Items::where('CategoryCode',$request->CategoryCode)->get(),
            'locations' => $location,
        ]);
    }

    //Add
    public function store(Request $request){
//return $request->details;
        $validator = Validator::make($request->all(), [
            'expenseDate' => 'required',
            'categoryType' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }
        else{
            try {
                DB::beginTransaction();

                //Data Insert ProductionMaster
                $expenseCode =  $this->generateExpenseCode();

                $expense = new ExpenseMaster();
                $expense->ExpenseCode = $expenseCode;
                $expense->HeadCode = $request->expenseHeadVal['HeadCode'];
                $expense->ExpenseDate = $request->expenseDate;
                $expense->CategoryCode =$request->categoryType['CategoryCode'];
                $expense->Naration = $request->narration;
                $expense->Amount = 0;
                $expense->PrepareDate = Carbon::now()->format('Y-m-d H:i:s');
                $expense->PrepareBy = Auth::user()->UserId;
                $expense->save();

                $totalAmount =0;

                if($request->details){
                    foreach ($request->details as $key=>$singleData){
                        $expenseDetails = new ExpenseDetails();
                        $expenseDetails->ExpenseCode = $expenseCode;
                        if($singleData['itemCode']===Null || $singleData['itemCode']==='' ){
                            $expenseDetails->ItemCode = '';
                        }
                        else{
                            $expenseDetails->ItemCode = $singleData['itemCode'];
                        }
                        $expenseDetails->LocationCode = $singleData['LocationCode'] ? $singleData['LocationCode']  :'';
                        $expenseDetails->rate = $singleData['rate'];
                        $expenseDetails->Quantity = $singleData['quantity'];
                        $expenseDetails->Amount = $singleData['itemValue'];
                        $totalAmount += $singleData['itemValue'];
                        $expenseDetails->save();
                    }
                }

                $addTotal = ExpenseMaster::where('ExpenseCode',$expenseCode)->first();
                $addTotal->Amount =  $totalAmount;
                $addTotal->save();



                DB::commit();
                return [
                    'status' => 'success',
                    'message' => 'Expense Created Successfully'
                ];

            } catch (\Exception $exception) {
                return response()->json([
                    'status' => 'error',
                    'message' => $exception->getMessage()
                ], 500);
            }
        }
    }
    public function getExpenseInfo($expenseCode){
        $expense =  ExpenseMaster::leftJoin('ExpenseDetails','ExpenseDetails.ExpenseCode','ExpenseMaster.ExpenseCode')
            ->leftJoin('ExpenseHead','ExpenseHead.HeadCode','ExpenseMaster.HeadCode')
            ->join('ItemsCategory','ItemsCategory.CategoryCode','ExpenseMaster.CategoryCode')
            ->leftJoin('Items','Items.ItemCode','ExpenseDetails.ItemCode')
            ->leftJoin('Location','Location.LocationCode','ExpenseDetails.LocationCode')
            ->where('ExpenseMaster.ExpenseCode',$expenseCode)
            ->select(
                'ExpenseMaster.ExpenseCode',
                'ExpenseMaster.HeadCode',
                'ExpenseHead.ExpenseHead',
                'ExpenseMaster.CategoryCode',
                'ExpenseDetails.LocationCode',
                'ExpenseDetails.ItemCode',
                DB::raw("convert(varchar(10),ExpenseMaster.ExpenseDate,23) as ExpenseDate"),
                'ItemsCategory.CategoryName',
                'Items.ItemName',
                'Items.UOM',
                'Location.LocationName',
                'ExpenseMaster.Naration',
                'ExpenseDetails.Rate',
                'ExpenseDetails.Quantity',
                'ExpenseDetails.Amount',
                DB::raw("convert(varchar(10),ExpenseMaster.PrepareDate,23) as PrepareDate"),
            )->get();

        return response()->json([
            'status' => 'success',
            'expenseInfo' => $expense
        ], 200);

    }

    public function update(Request $request){

        if($request->expenseCode){
            $validator = Validator::make($request->all(), [
                'expenseCode' => 'required',
                'expenseDate' => 'required',
                'categoryType' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 400);
            }
            else{
                try {
                    $expense = ExpenseMaster::where('ExpenseCode',$request->expenseCode)->first();
                    $expense->HeadCode = (isset($request->expenseHeadVal[0]['HeadCode']))? $request->expenseHeadVal[0]['HeadCode']: $request->expenseHeadVal['HeadCode'] ;
                    $expense->ExpenseDate = $request->expenseDate;
                    $expense->CategoryCode = (isset($request->categoryType[0]['CategoryCode']))?$request->categoryType[0]['CategoryCode']:$request->categoryType[0]['CategoryCode'];
                    $expense->Naration = $request->narration;
                    $expense->Amount = 0;
                    $expense->EditDate = Carbon::now()->format('Y-m-d H:i:s');
                    $expense->EditBy =Auth::user()->UserId;
                    $expense->save();

                    $totalAmount =0;

                    //delete existing One
                    ExpenseDetails::where('ExpenseCode',$request->expenseCode)->delete();

                    if($request->details){

                        foreach ($request->details as $key=>$singleData){
                            $expenseDetails = new ExpenseDetails();
                            $expenseDetails->ExpenseCode = $request->expenseCode;
                            if($singleData['itemCode']===Null || $singleData['itemCode']==='' ){
                                $expenseDetails->ItemCode = '';
                            }
                            else{
                                $expenseDetails->ItemCode = $singleData['itemCode'];
                            }
                            $expenseDetails->LocationCode = $singleData['LocationCode']? $singleData['LocationCode']:'';
                            $expenseDetails->rate = $singleData['rate']? $singleData['rate']:0;
                            $expenseDetails->Quantity = $singleData['quantity']? $singleData['quantity']:0;
                            $expenseDetails->Amount = $singleData['itemValue']? $singleData['itemValue']:0;
                            $totalAmount += $singleData['itemValue'];
                            $expenseDetails->save();
                        }
                    }

                    $addTotal = ExpenseMaster::where('ExpenseCode',$request->expenseCode)->first();
                    $addTotal->Amount =  $totalAmount;
                    $addTotal->save();

                    DB::commit();
                    return [
                        'status' => 'success',
                        'message' => 'Expense Updated Successfully'
                    ];

                } catch (\Exception $exception) {
                    return response()->json([
                        'status' => 'error',
                        'message' => $exception->getMessage()
                    ], 500);
                }
            }
        }

    }


    //Month Closing
    public function addMonthClosing( Request  $request){

         $date=date_create($request->monthClosingDate);
        $period =date_format($date,"Ym");

         try{

             $monthClosing = DB::select("exec sp_ClosingBalance '$period'");

             return response()->json([
                 'message' => 'Month Closed Successfully',
             ]);
         }
         catch (\Exception $exception){
             return response()->json([
                 'status' => 'error',
                 'message' => $exception->getMessage()
             ], 500);
         }

    }



}
