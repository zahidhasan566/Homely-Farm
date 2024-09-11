<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\CategoryLocation;
use App\Models\Items;
use App\Models\ItemsCategory;
use App\Models\Location;
use App\Models\ProductionDetails;
use App\Models\ProductionMaster;
use App\Models\PurchaseDetails;
use App\Models\PurchaseMaster;
use App\Models\StockBatch;
use App\Traits\CodeGeneration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductionController extends Controller
{
    use CodeGeneration;
    public function getSupportingData(){
        return response()->json([
            'status' => 'success',
            'category' => ItemsCategory::where('Active','Y')->get(),
        ]);
    }
    public function index(Request $request){
        $take = $request->take;
        $search = $request->search;
        $productionMaster =  ProductionMaster::join('ProductionDetails', 'ProductionDetails.ProductionCode', 'ProductionMaster.ProductionCode')
                                  ->join('ItemsCategory','ItemsCategory.CategoryCode','ProductionMaster.CategoryCode')
                                  ->join('Items','Items.CategoryCode','ItemsCategory.CategoryCode')
                                  ->where(function ($q) use ($search) {
                                    $q->where('ProductionMaster.ProductionCode', 'like', '%' . $search . '%');
                                    $q->orWhere('ProductionMaster.ProductionDate', 'like', '%' . $search . '%');
                                 })
                                ->where('ProductionMaster.Returned','!=','Y')

          //  ->orderBy('ProductionMaster.PrepareDate', 'desc')
            ->select(
                'ProductionMaster.ProductionCode',
                DB::raw("convert(varchar(10),ProductionMaster.ProductionDate,23) as ProductionDate"),
                'ProductionMaster.Reference',
                'ItemsCategory.CategoryName',
                DB::raw("(CASE WHEN ProductionMaster.Returned = 'Y' THEN 'Yes' ELSE 'No' END) AS Returned"),

                DB::raw("convert(varchar(10),ProductionMaster.PrepareDate,23) as PrepareDate"),
                )
            ->groupBy(
                'ProductionMaster.ProductionCode',
                'ProductionMaster.ProductionDate',
                'ProductionMaster.Reference',
                'ItemsCategory.CategoryName',
                'ProductionMaster.Returned',
                'ProductionMaster.PrepareDate')
            ->paginate($take);
        return $productionMaster;
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

    //Add Data
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'production_date' => 'required',
            'categoryType' => 'required',
            'details' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }
        else{
            try {
                DB::beginTransaction();

                //Data Insert ProductionMaster
                $productionCode =  $this->generateProductionMasterCode();

                $dataProduction = new ProductionMaster();
                $dataProduction->ProductionCode = $productionCode;
                $dataProduction->ProductionDate = $request->production_date;
                $dataProduction->Reference = $request->reference;
                $dataProduction->CategoryCode =$request->categoryType['CategoryCode'];
                $dataProduction->Returned = 'N';
                $dataProduction->PrepareDate = Carbon::now()->format('Y-m-d H:i:s');;
                $dataProduction->PrepareBy = Auth::user()->UserId;
                $dataProduction->save();

                foreach ($request->details as $key=>$singleData){
                    $productionDetails = new ProductionDetails();
                    $productionDetails->ProductionCode = $productionCode;
                    $productionDetails->ItemCode = $singleData['itemCode'];
                    $productionDetails->LocationCode = $singleData['LocationCode'];
                    $productionDetails->Quantity = $singleData['quantity'];
                    $productionDetails->Value = $singleData['itemValue'];
                    $productionDetails->save();

                    //Data insert into Stock Batch
                    $checkExisting =StockBatch::where('ItemCode', $singleData['itemCode'])->where('LocationCode',$singleData['LocationCode'])->orderBy('ItemCode','desc')->first();
                    if($checkExisting){
                        $existingReceiveQty = $checkExisting->ReceiveQty;
                        $existingBatchQty = $checkExisting->BatchQty;
                        $existingStockValue = $checkExisting->StockValue;
                            StockBatch::where('ItemCode', $singleData['itemCode'])->where('LocationCode',$singleData['LocationCode'])->update([
                                'ReceiveQty'=>$existingReceiveQty + $singleData['quantity'],
                                'BatchQty'=>$existingBatchQty + $singleData['quantity'],
                            ]);

                    }
                    else{
                        $stockBatch= new StockBatch();
                        $stockBatch->ItemCode = $singleData['itemCode'];
                        $stockBatch->LocationCode = $singleData['LocationCode'];
                        $stockBatch->ReceiveQty = $singleData['quantity'];
                        $stockBatch->BatchQty = $singleData['quantity'];
                        $stockBatch->StockValue = $singleData['quantity'];
                        $stockBatch->save();
                    }

                }
                DB::commit();
                return [
                    'status' => 'success',
                    'message' => 'production Created Successfully'
                ];

            } catch (\Exception $exception) {
                return response()->json([
                    'status' => 'error',
                    'message' => $exception->getMessage()
                ], 500);
            }
        }
    }

    //GET PRODUCTION INFO
    public function getProductionInfo($productionCode){
        $singleProductionMaster =  ProductionMaster::join('ProductionDetails', 'ProductionDetails.ProductionCode', 'ProductionMaster.ProductionCode')
            ->join('ItemsCategory','ItemsCategory.CategoryCode','ProductionMaster.CategoryCode')
            ->join('Items',function ($q) {
                $q->on('Items.ItemCode','ProductionDetails.ItemCode');
                    //->where('Items.CategoryCode',)
            })
            ->join('Location',function ($q) {
                $q->on('Location.LocationCode','ProductionDetails.LocationCode');
                //->where('Items.CategoryCode',)
            })
            //->join('Items','Items.CategoryCode','ItemsCategory.CategoryCode')
            ->where('ProductionMaster.ProductionCode',$productionCode)
            ->select(
                'ProductionMaster.ProductionCode',
                 DB::raw("convert(varchar(10),ProductionMaster.ProductionDate,23) as ProductionDate"),
                //'ProductionMaster.ProductionDate',
                'ProductionMaster.Reference',
                'ItemsCategory.CategoryName',
                'ProductionMaster.Returned',
                'ProductionMaster.PrepareDate',

                'ProductionDetails.ItemCode',
                'ProductionDetails.LocationCode',
                'ProductionDetails.Quantity',
                'ProductionDetails.Value',

                'ItemsCategory.CategoryCode',
                'ItemsCategory.CategoryName',
                'ItemsCategory.Active',
                'Items.ItemName',
                'Items.UOM',
                'Location.LocationName',

            )
            ->get();

        return response()->json([
            'status' => 'success',
            'ProductionInfo' => $singleProductionMaster
        ], 200);
    }


    //Update Product
    public function update(Request $request){

        if($request->production_code){
            $validator = Validator::make($request->all(), [
                'production_date' => 'required',
                'categoryType' => 'required',
                'details' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 400);
            }
            else{
                try {

                    $dataProduction = ProductionMaster::where('ProductionCode',$request->production_code)->first();
                    $dataProduction->ProductionDate = $request->production_date;
                    $dataProduction->Reference = $request->reference;
                    $dataProduction->Returned = 'N';
                    $dataProduction->EditDate = Carbon::now()->format('Y-m-d H:i:s');;
                    $dataProduction->EditBy = Auth::user()->UserId;
                    $dataProduction->save();

                    foreach ($request->details as $key=>$singleData){

                        //Data insert into Stock Batch
                        $existingProductionDetail =  ProductionDetails::where('ProductionCode',$request->production_code)->where('LocationCode',$singleData['location']['LocationCode'])
                            ->where('ItemCode',$singleData['itemCode'])->first();

                        $existingStockTable = StockBatch::where('ItemCode',$singleData['itemCode'])
                            ->where('LocationCode',$singleData['location']['LocationCode'])
                            ->first();

                        if($existingStockTable){
                            $existingReceiveQty = $existingStockTable->ReceiveQty;
                            $existingBatchQty = $existingStockTable->BatchQty;
                            $existingStockValue = $existingStockTable->StockValue;

                            if($existingProductionDetail['Quantity']>$singleData['quantity']){
                                $UpdateLessQuantity = $existingProductionDetail['Quantity'] -$singleData['quantity'];
                                //Back Existing Product
                                StockBatch::where('ItemCode', $singleData['itemCode'])->where('LocationCode',$singleData['location']['LocationCode'])->update([
                                    'ReceiveQty'=>floatval($existingReceiveQty) - floatval($UpdateLessQuantity),
                                    'BatchQty'=>floatval($existingBatchQty) -  floatval($UpdateLessQuantity),
                                ]);

                            }
                            else{
                                $UpdateGreaterQuantity = $singleData['quantity'] - $existingProductionDetail['Quantity']  ;
                                StockBatch::where('ItemCode', $singleData['itemCode'])->where('LocationCode',$singleData['location']['LocationCode'])->update([
                                    'ReceiveQty'=>floatval($existingReceiveQty) + floatval($UpdateGreaterQuantity),
                                    'BatchQty'=>floatval($existingBatchQty) +  floatval($UpdateGreaterQuantity)
                                ]);
                            }
                        }
                        else{
                            $stockBatch= new StockBatch();
                            $stockBatch->ItemCode = $singleData['itemCode'];
                            $stockBatch->LocationCode = $singleData['LocationCode'];
                            $stockBatch->ReceiveQty = $singleData['quantity'];
                            $stockBatch->BatchQty = $singleData['quantity'];
                            $stockBatch->StockValue = $singleData['quantity'];
                            $stockBatch->save();
                        }

                        //Data Insert ProductionDetails
                        if($existingProductionDetail){
                            $productionDetails =  ProductionDetails::where('ProductionCode',$request->production_code)
                                ->where('ItemCode',$singleData['itemCode'])->delete();
                        }

                        $productionDetails= new ProductionDetails();
                        $productionDetails->ProductionCode = $dataProduction->ProductionCode;
                        $productionDetails->ItemCode = $singleData['itemCode'];
                        $productionDetails->LocationCode = $singleData['LocationCode'];
                        $productionDetails->Quantity = $singleData['quantity'];
                        $productionDetails->Value = $singleData['itemValue'];
                        $productionDetails->save();


                    }
                    DB::commit();
                    return [
                        'status' => 'success',
                        'message' => 'production Updated Successfully'
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

    //Return Product
    public function returnProducts(Request $request){
        if($request->production_code){
            try {
                DB::beginTransaction();

                //Data Insert PurchaseMaster
                $dataProduction = ProductionMaster::where('ProductionCode',$request->production_code)->first();
                $dataProduction->Returned = 'Y';
                $dataProduction->EditDate = Carbon::now()->format('Y-m-d H:i:s');;
                $dataProduction->EditBy = Auth::user()->UserId;
                $dataProduction->save();


                $existingProductionDetails =  ProductionDetails::where('ProductionCode',$request->production_code)->get();
                foreach ($existingProductionDetails as $key=>$singleData){


                    //Data update  into Stock Batch
                    $existingStockTable = StockBatch::where('ItemCode', $singleData->ItemCode)->where('LocationCode',$singleData['LocationCode'])->first();
                    if($existingStockTable){
                        $existingReceiveQty = $existingStockTable->ReceiveQty;
                        $existingBatchQty = $existingStockTable->BatchQty;
                        $existingStockValue = $existingStockTable->StockValue;
                        StockBatch::where('ItemCode', $singleData->ItemCode)->where('LocationCode',$singleData['LocationCode'])->update([
                            'ReceiveQty'=>floatval($existingBatchQty) - floatval($singleData->Quantity),
                            'BatchQty'=>floatval($existingBatchQty) - floatval($singleData->Quantity),
                        ]);
                    }
                }
                DB::commit();
                return [
                    'status' => 'success',
                    'message' => 'Production Returned Successfully'
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
