<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use App\Models\CategoryLocation;
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

    public function getSupportingData()
    {
        $customers = Customer::all();
        $finalCustomer = [];
        $data = [];
        foreach ($customers as $single_data) {
            $data['CustomerWithCode'] = $single_data->CustomerCode . '-' . $single_data->CustomerName;
            $data['CustomerCode'] = $single_data->CustomerCode;
            array_push($finalCustomer, $data);
        }
        $totalStock = StockBatch::select('ItemCode','LocationCode', DB::raw("SUM(BatchQty) as stock"))->groupBy('ItemCode','LocationCode')->get();



        return response()->json([
            'status' => 'success',
            'category' => ItemsCategory::where('Active','Y')->get(),
            'customer' => $finalCustomer,
            'allStock' => $totalStock,
            'locations' => Location::all(),
        ]);
    }

    public function index(Request $request)
    {
        $take = $request->take;
        $search = $request->search;
        $salesMaster = SalesMaster::join('SalesDetails', 'SalesDetails.SalesCode', 'SalesMaster.SalesCode')
            ->join('ItemsCategory', 'ItemsCategory.CategoryCode', 'SalesMaster.CategoryCode')
            ->join('Customer', 'Customer.CustomerCode', 'SalesMaster.CustomerCode')
            ->where(function ($q) use ($search) {
                $q->where('SalesMaster.SalesCode', 'like', '%' . $search . '%');
                $q->orWhere('SalesMaster.SalesDate', 'like', '%' . $search . '%');
            })
            ->where('SalesMaster.Returned','!=','Y')
            ->orderBy('SalesMaster.PrepareDate', 'desc')
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
    public function getCategoryWiseItemData(Request $request)
    {
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
            'items' => Items::where('CategoryCode', $request->CategoryCode)->get(),
            'locations' => $location,
        ]);
    }

    //Check Stock
    public function getCategoryWiseItemStock(Request $request)
    {

        $totalStock = StockBatch::select('ItemCode', DB::raw("SUM(BatchQty) as stock"))->where('ItemCode', $request->ItemCode)->groupBy('ItemCode')->first();
        $totalStock = floatval($totalStock->stock);
        return response()->json([
            'status' => 'success',
            'totalStock' => $totalStock,
        ]);
    }

    //Store Sales
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sales_date' => 'required',
            'categoryType' => 'required',
            'customerTypeVal' => 'required',
            'details' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        } else {
            try {
                DB::beginTransaction();

                //Data Insert PurchaseMaster
                $salesCode = $this->generateSalesMasterCode();

                $dataSales = new SalesMaster();
                $dataSales->SalesCode = $salesCode;
                $dataSales->SalesDate = $request->sales_date;
                $dataSales->Reference = $request->reference;
                $dataSales->CategoryCode = $request->categoryType['CategoryCode'];
                $dataSales->CustomerCode = $request->customerTypeVal['CustomerCode'];
                $dataSales->Returned = 'N';
                $dataSales->PrepareDate = Carbon::now()->format('Y-m-d H:i:s');;
                $dataSales->PrepareBy = Auth::user()->Id;
                $dataSales->save();

                foreach ($request->details as $key => $singleData) {

                    //Data Insert ProductionDetails
                    $dataSalesDetails = new SalesDetails();
                    $dataSalesDetails->SalesCode = $salesCode;
                    $dataSalesDetails->ItemCode = $singleData['itemCode'];
                    $dataSalesDetails->LocationCode = $singleData['LocationCode'];
                    $dataSalesDetails->unitPrice = $singleData['unitPrice'];
                    $dataSalesDetails->Quantity = $singleData['quantity'];
                    $dataSalesDetails->Value = $singleData['itemValue'];
                    $dataSalesDetails->save();

                    //Data insert into Stock Batch
                    $checkExisting = StockBatch::where('ItemCode', $singleData['itemCode'])->where('LocationCode', $singleData['LocationCode'])->orderBy('ItemCode', 'desc')->first();
                    if ($checkExisting) {
                        $existingBatchQty = $checkExisting->BatchQty;
                        $existingStockValue = $checkExisting->StockValue;
                        StockBatch::where('ItemCode', $singleData['itemCode'])->where('LocationCode', $singleData['LocationCode'])->update([
                            'BatchQty' => $existingBatchQty - $singleData['quantity'],
                        ]);

                    }
                }
                DB::commit();
                return [
                    'status' => 'success',
                    'message' => 'Sales Created Successfully'
                ];

            } catch (\Exception $exception) {
                return response()->json([
                    'status' => 'error',
                    'message' => $exception->getMessage()
                ], 500);
            }
        }
    }

    public function getSalesInfo($salesCode)
    {
        $singleSales = SalesMaster::join('SalesDetails', 'SalesDetails.SalesCode', 'SalesMaster.SalesCode')
            ->join('ItemsCategory', 'ItemsCategory.CategoryCode', 'SalesMaster.CategoryCode')
            ->join('Items', function ($q) {
                $q->on('Items.ItemCode', 'SalesDetails.ItemCode');
            })
            ->join('Location', function ($q) {
                $q->on('Location.LocationCode', 'SalesDetails.LocationCode');
            })
            ->join('Customer', 'Customer.CustomerCode', 'SalesMaster.CustomerCode')
            ->leftJoin('StockBatch', function ($q) {
                $q->on('StockBatch.ItemCode', 'SalesDetails.ItemCode')->on('StockBatch.LocationCode', 'SalesDetails.LocationCode');
            })
            ->where('SalesMaster.SalesCode', $salesCode)
            ->select(
                'SalesMaster.SalesCode',
                DB::raw("convert(varchar(10),SalesMaster.SalesDate,23) as SalesDate"),
                'SalesMaster.Reference',
                'SalesMaster.CategoryCode',
                'ItemsCategory.CategoryName',
                'Customer.CustomerName',
                'Customer.CustomerCode',
                DB::raw("(CASE WHEN Customer.CustomerCode IS NOT NULL THEN (Customer.CustomerCode +'-'+Customer.CustomerName) END) AS CustomerWithCode"),
                'SalesMaster.Returned',
                DB::raw("convert(varchar(10),SalesMaster.PrepareDate,23) as PrepareDate"),
                'SalesDetails.ItemCode',
                'SalesDetails.LocationCode',
                'SalesDetails.UnitPrice',
                'SalesDetails.Quantity',
                'SalesDetails.Value',
                DB::raw("SUM(StockBatch.BatchQTY) as stock"),
                'Items.ItemName',
                'Items.UOM',
                'Location.LocationName'
            )
            ->groupBy('SalesMaster.SalesCode',
                'SalesMaster.SalesDate',
                'SalesMaster.Reference',
                'SalesMaster.CategoryCode',
                'ItemsCategory.CategoryName',
                'Customer.CustomerName',
                'Customer.CustomerCode',
                'SalesMaster.Returned',
                'SalesMaster.PrepareDate',
                'SalesDetails.ItemCode',
                'SalesDetails.LocationCode',
                'SalesDetails.UnitPrice',
                'SalesDetails.Quantity',
                'SalesDetails.Value',
                'Items.ItemName',
                'Items.UOM',
                'Location.LocationName')
            ->get();

        return response()->json([
            'status' => 'success',
            'SalesInfo' => $singleSales
        ], 200);
    }

    //Update
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sales_date' => 'required',
            'categoryType' => 'required',
            'customerTypeVal' => 'required',
            'details' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        } else {
            try {
                DB::beginTransaction();

                $dataSales = SalesMaster::where('SalesCode', $request->salesCode)->first();
                $dataSales->SalesDate = $request->sales_date;
                $dataSales->Reference = $request->reference;
                $dataSales->CustomerCode = $request->customerTypeVal['CustomerCode'];
                $dataSales->Returned = 'N';
                $dataSales->EditDate = Carbon::now()->format('Y-m-d H:i:s');;
                $dataSales->EditBy = Auth::user()->Id;
                $dataSales->save();


                //delete and insert the existing
                foreach ($request->details as $key => $singleData) {

                    $existingSalesDetail = SalesDetails::where('SalesCode', $request->salesCode)
                        ->where('ItemCode', $singleData['itemCode'])
                        ->where('LocationCode', $singleData['LocationCode'])->first();

                    //Data update  into Stock Batch
                    $existingStockTable = StockBatch::where('ItemCode',$singleData['itemCode'])
                        ->where('LocationCode',$singleData['LocationCode'])
                        ->first();

                    if($existingStockTable){
                        $existingBatchQty = $existingStockTable->BatchQty;
                        $existingStockValue = $existingStockTable->StockValue;

                        if($existingSalesDetail['Quantity']>$singleData['quantity']) {
                            $UpdateLessQuantity = $existingSalesDetail['Quantity'] - $singleData['quantity'];
                            //Back Existing Product
                            StockBatch::where('ItemCode', $singleData['itemCode'])->where('LocationCode', $singleData['LocationCode'])->update([
                                'BatchQty' => floatval($existingBatchQty) + floatval($UpdateLessQuantity),
                            ]);
                        }
                        else{

                            $UpdateGreaterQuantity = $singleData['quantity'] - $existingSalesDetail['Quantity']  ;
                            StockBatch::where('ItemCode', $singleData['itemCode'])->where('LocationCode',$singleData['LocationCode'])->update([
                                'BatchQty'=>floatval($existingBatchQty) -  floatval($UpdateGreaterQuantity),
                            ]);

                        }
                    }
                    if($existingSalesDetail){
                        $existingPurchaseDetails = SalesDetails::where('SalesCode', $request->salesCode)
                            ->where('ItemCode', $singleData['itemCode'])
                            ->where('LocationCode', $singleData['LocationCode'])
                            ->delete();
                    }



                    //Data Insert ProductionDetails
                    $dataSalesDetails = new SalesDetails();
                    $dataSalesDetails->SalesCode = $dataSales->SalesCode;
                    $dataSalesDetails->ItemCode = $singleData['itemCode'];
                    $dataSalesDetails->LocationCode = $singleData['LocationCode'];
                    $dataSalesDetails->unitPrice = $singleData['unitPrice'];
                    $dataSalesDetails->Quantity = $singleData['quantity'];
                    $dataSalesDetails->Value = $singleData['itemValue'];
                    $dataSalesDetails->save();
                }
                DB::commit();
                return [
                    'status' => 'success',
                    'message' => 'Sales Updated Successfully'
                ];

            } catch (\Exception $exception) {
                return response()->json([
                    'status' => 'error',
                    'message' => $exception->getMessage()
                ], 500);
            }
        }
    }

    //Return
    public function returnProducts(Request $request){
        if($request->salesCode){
            try {
                DB::beginTransaction();

                //Data Insert SalesMaster
                $dataPurchase = SalesMaster::where('SalesCode',$request->salesCode)->first();
                $dataPurchase->Returned = 'Y';
                $dataPurchase->EditDate = Carbon::now()->format('Y-m-d H:i:s');;
                $dataPurchase->EditBy = Auth::user()->Id;
                $dataPurchase->save();

                $existingSalesDetails =  SalesDetails::where('SalesCode',$request->salesCode)->get();

                foreach ($existingSalesDetails as $key=>$singleData){
                    //Data update  into Stock Batch
                    $existingStockTable = StockBatch::where('ItemCode', $singleData->ItemCode)->where('LocationCode',$singleData['LocationCode'])->first();
                    if($existingStockTable){
                        $existingBatchQty = $existingStockTable->BatchQty;
                        $existingStockValue = $existingStockTable->StockValue;

                        StockBatch::where('ItemCode', $singleData->ItemCode)->where('LocationCode',$singleData['LocationCode'])->update([
                            'BatchQty'=>floatval($existingBatchQty) + floatval($singleData->Quantity),
                        ]);
                    }
                }
                DB::commit();
                return [
                    'status' => 'success',
                    'message' => 'Sales Returned Successfully'
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
