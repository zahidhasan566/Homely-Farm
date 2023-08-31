<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Items;
use App\Models\ItemsCategory;
use App\Models\Location;
use App\Models\PurchaseDetails;
use App\Models\PurchaseMaster;
use App\Models\SalesDetails;
use App\Models\SalesMaster;
use App\Models\StockBatch;
use App\Traits\CodeGeneration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SalesController extends Controller
{
    use CodeGeneration;
    public function getSupportingData(){
        $customers = Customer::all();
        $finalCustomer =[];
        $data=[];
        foreach ($customers as $single_data){
            $data['CustomerWithCode'] = $single_data->CustomerCode.'-'.$single_data->CustomerName;
            $data['CustomerCode'] = $single_data->CustomerCode;
            array_push($finalCustomer,$data);
        }

        return response()->json([
            'status' => 'success',
            'category' => ItemsCategory::all(),
            'customer' => $finalCustomer,
            'locations' => Location::all(),
        ]);
    }
    public function index(Request $request){
        $take = $request->take;
        $search = $request->search;
        $salesMaster =  SalesMaster::join('SalesDetails', 'SalesDetails.SalesCode', 'SalesMaster.SalesCode')
            ->join('ItemsCategory','ItemsCategory.CategoryCode','SalesMaster.CategoryCode')
            ->join('Customer','Customer.CustomerCode','SalesMaster.CustomerCode')
            ->where(function ($q) use ($search) {
                $q->where('SalesMaster.SalesCode', 'like', '%' . $search . '%');
                $q->orWhere('SalesMaster.SalesDate', 'like', '%' . $search . '%');
            })

            ->orderBy('SalesMaster.SalesDate', 'desc')
            ->select(
                'SalesMaster.SalesCode',
                DB::raw("convert(varchar(10),SalesMaster.SalesDate,23) as SalesDate"),
                'SalesMaster.Reference',
                'SalesMaster.CategoryCode',
                'ItemsCategory.CategoryName',
                'Customer.CustomerName',
                'SalesMaster.Returned',
                DB::raw("convert(varchar(10),SalesMaster.PrepareDate,23) as PrepareDate"),
            )
            ->groupBy(
                'SalesMaster.SalesCode',
                'SalesMaster.SalesDate',
                'SalesMaster.Reference',
                'SalesMaster.CategoryCode',
                'ItemsCategory.CategoryName',
                'Customer.CustomerName',
                'SalesMaster.Returned',
                'SalesMaster.PrepareDate'
            )
            ->paginate($take);
        return $salesMaster;
    }

    //Category Wise Item
    public function getCategoryWiseItemData(Request $request){
        return response()->json([
            'status' => 'success',
            'items' => Items::where('CategoryCode',$request->CategoryCode)->get(),
            'locations' => Location::all(),
        ]);
    }

    //Store Sales
    //Store Purchase
    public function store(Request $request){
        dd($request);
        $validator = Validator::make($request->all(), [
            'sales_date' => 'required',
            'reference' => 'required',
            'categoryType' => 'required',
            'customerTypeVal' => 'required',
            'details' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }
        else{
            try {
                DB::beginTransaction();

                //Data Insert PurchaseMaster
                $salesCode =  $this->generateSalesMasterCode();

                $dataSales = new SalesMaster();
                $dataSales->SalesCode = $salesCode;
                $dataSales->SalesDate = $request->sales_date;
                $dataSales->Reference = $request->reference;
                $dataSales->CategoryCode =$request->categoryType['CategoryCode'];
                $dataSales->CustomerCode =$request->customerTypeVal['CustomerCode'];
                $dataSales->Returned = 'N';
                $dataSales->PrepareDate = Carbon::now()->format('Y-m-d H:i:s');;
                $dataSales->PrepareBy = Auth::user()->Id;
                $dataSales->save();

                foreach ($request->details as $key=>$singleData){

                    //Data Insert ProductionDetails
                    $dataSalesDetails = new SalesDetails();
                    $dataSalesDetails->SalesCode = $salesCode;
                    $dataSalesDetails->ItemCode = $singleData['item']['ItemCode'];
                    $dataSalesDetails->LocationCode = $singleData['location']['LocationCode'];
                    $dataSalesDetails->unitPrice = $singleData['unitPrice'];
                    $dataSalesDetails->Quantity = $singleData['quantity'];
                    $dataSalesDetails->Value = $singleData['itemValue'];
                    $dataSalesDetails->save();

                    //Data insert into Stock Batch
                    $existingStockTable = StockBatch::where('ItemCode', $singleData['item']['ItemCode'])->where('LocationCode',$singleData['location']['LocationCode'])->first();
                    if($existingStockTable){
                        $existingBatchQty = $existingStockTable->BatchQty;
                        $existingStockValue = $existingStockTable->StockValue;
                        StockBatch::where('ItemCode', $singleData['item']['ItemCode'])->where('LocationCode','L0001')->update([
                            'BatchQty'=>$existingBatchQty - $singleData['quantity'],
                            'StockValue'=>$existingStockValue - $singleData['quantity'],
                        ]);
                    }
                    else{
                        $stockBatch= new StockBatch();
                        $stockBatch->ItemCode = $singleData['item']['ItemCode'];
                        $stockBatch->LocationCode = $singleData['location']['LocationCode'];;
                        $stockBatch->BatchQty = $singleData['quantity'];
                        $stockBatch->StockValue =  $singleData['itemValue'];
                        $stockBatch->save();
                    }
                }
                DB::commit();
                return [
                    'status' => 'success',
                    'message' => 'Purchase Created Successfully'
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
