<?php

namespace App\Http\Controllers\Expense;

use App\Http\Controllers\Controller;
use App\Models\CategoryLocation;
use App\Models\Employee;
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
            $expense =  ExpenseMaster::join('ExpenseHead','ExpenseHead.HeadCode','ExpenseMaster.HeadCode')
                ->join('ItemsCategory','ItemsCategory.CategoryCode','ExpenseMaster.CategoryCode')
                ->join('Items','Items.ItemCode','ExpenseMaster.ItemCode')
                ->join('Location','Location.LocationCode','ExpenseMaster.LocationCode')
                ->where(function ($q) use ($search) {
                $q->where('ExpenseMaster.ExpenseCode', 'like', '%' . $search . '%');
                $q->orWhere('ExpenseHead.HeadCode', 'like', '%' . $search . '%');
                $q->orWhere('ExpenseMaster.ExpenseDate', 'like', '%' . $search . '%');
            })
                ->orderBy('ExpenseMaster.ExpenseCode','desc')
                ->select(
                    'ExpenseMaster.ExpenseCode',
                    'ExpenseHead.ExpenseHead',
                    DB::raw("convert(varchar(10),ExpenseMaster.ExpenseDate,23) as ExpenseDate"),
                    'ItemsCategory.CategoryName',
                    'Items.ItemName',
                    'Location.LocationName',
                    'ExpenseMaster.Naration',
                    'ExpenseMaster.Rate',
                    'ExpenseMaster.Quantity',
                    'ExpenseMaster.Amount',
                    DB::raw("convert(varchar(10),ExpenseMaster.PrepareDate,23) as PrepareDate"),

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

                $expense->CategoryCode =$request->categoryType['CategoryCode'];;

                $expense->ItemCode = ($request->details) ? $request->details[0]['itemCode'] :'' ;
                $expense->LocationCode = ($request->details) ? $request->details[0]['location']['LocationCode']:'';
                $expense->Naration = $request->narration;
                $expense->Rate = $request->rate;
                $expense->Quantity = ($request->details) ? $request->details[0]['quantity']:'';
                $expense->Amount = ($request->details) ? $request->details[0]['itemValue']:'';
                $expense->PrepareDate = Carbon::now()->format('Y-m-d H:i:s');
                $expense->PrepareBy = Auth::user()->Id;
                $expense->save();

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
        $expense =  ExpenseMaster::join('ExpenseHead','ExpenseHead.HeadCode','ExpenseMaster.HeadCode')
            ->join('ItemsCategory','ItemsCategory.CategoryCode','ExpenseMaster.CategoryCode')
            ->join('Items','Items.ItemCode','ExpenseMaster.ItemCode')
            ->join('Location','Location.LocationCode','ExpenseMaster.LocationCode')
            ->where('ExpenseMaster.ExpenseCode',$expenseCode)
            ->select(
                'ExpenseMaster.ExpenseCode',
                'ExpenseMaster.HeadCode',
                'ExpenseHead.ExpenseHead',
                'ExpenseMaster.CategoryCode',
                'ExpenseMaster.LocationCode',
                'ExpenseMaster.ItemCode',
                DB::raw("convert(varchar(10),ExpenseMaster.ExpenseDate,23) as ExpenseDate"),
                'ItemsCategory.CategoryName',
                'Items.ItemName',
                'Items.UOM',
                'Location.LocationName',
                'ExpenseMaster.Naration',
                'ExpenseMaster.Rate',
                'ExpenseMaster.Quantity',
                'ExpenseMaster.Amount',
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

                    $expense->HeadCode = $request->expenseHeadVal[0]['HeadCode'] ? $request->expenseHeadVal[0]['HeadCode']:$request->expenseHeadVal['HeadCode'];
                    $expense->ExpenseDate = $request->expenseDate;

                    $expense->ItemCode = ($request->details) ? $request->details[0]['itemCode'] :'' ;
                    $expense->LocationCode = ($request->details) ? $request->details[0]['LocationCode']:'';
                    $expense->Naration = $request->narration;
                    $expense->Rate = $request->rate;
                    $expense->Quantity = ($request->details) ? $request->details[0]['quantity']:'';
                    $expense->Amount = ($request->details) ? $request->details[0]['itemValue']:'';
                    $expense->EditDate = Carbon::now()->format('Y-m-d H:i:s');
                    $expense->EditBy = Auth::user()->Id;
                    $expense->save();

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



}
