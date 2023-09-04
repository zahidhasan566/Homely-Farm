<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
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

class PurchaseController extends Controller
{
    use CodeGeneration;
    public function getSupportingData(){

        return response()->json([
            'status' => 'success',
            'category' => ItemsCategory::all(),
        ]);
    }
    public function index(Request $request){
        $take = $request->take;
        $search = $request->search;
        $purchaseMaster =  PurchaseMaster::join('PurchaseDetails', 'PurchaseDetails.PurchaseCode', 'PurchaseMaster.PurchaseCode')
            ->join('ItemsCategory','ItemsCategory.CategoryCode','PurchaseMaster.CategoryCode')
            ->where(function ($q) use ($search) {
                $q->where('PurchaseMaster.PurchaseCode', 'like', '%' . $search . '%');
                $q->orWhere('PurchaseMaster.Preparedate', 'like', '%' . $search . '%');
            })

            ->orderBy('PurchaseMaster.Preparedate', 'desc')
            ->select(
                'PurchaseMaster.PurchaseCode',
                DB::raw("convert(varchar(10),PurchaseMaster.PurchaseDate,23) as PurchaseDate"),
                'PurchaseMaster.Reference',
                'ItemsCategory.CategoryName',
                'PurchaseMaster.Returned',
                DB::raw("convert(varchar(10),PurchaseMaster.Preparedate,23) as Preparedate"),
            )
            ->groupBy(
                'PurchaseMaster.PurchaseCode',
                'PurchaseMaster.PurchaseDate',
                'PurchaseMaster.Reference',
                'ItemsCategory.CategoryName',
                'PurchaseMaster.Returned',
                'PurchaseMaster.Preparedate'
            )
            ->paginate($take);
        return $purchaseMaster;
    }
    public function getCategoryWiseItemData(Request $request){
        return response()->json([
            'status' => 'success',
            'items' => Items::where('CategoryCode',$request->CategoryCode)->get(),
            'locations' => Location::all(),
        ]);
    }

    //Store Purchase
    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'purchase_date' => 'required',
            'categoryType' => 'required',
            'details' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }
        else{
            try {
                DB::beginTransaction();

                //Data Insert PurchaseMaster
                $purchaseCodeCode =  $this->generatePurchaseMasterCode();

                $dataPurchase = new PurchaseMaster();
                $dataPurchase->PurchaseCode = $purchaseCodeCode;
                $dataPurchase->PurchaseDate = $request->purchase_date;
                $dataPurchase->Reference = $request->reference;
                $dataPurchase->CategoryCode =$request->categoryType['CategoryCode'];
                //$dataPurchase->PurchaseType =$request->purchaseTypeVal['PurchaseTypeCode'];
                $dataPurchase->Returned = 'N';
                $dataPurchase->PrepareDate = Carbon::now()->format('Y-m-d H:i:s');;
                $dataPurchase->PrepareBy = Auth::user()->Id;
                $dataPurchase->save();

                foreach ($request->details as $key=>$singleData){

                    //Data Insert ProductionDetails
                    $purchaseDetails = new PurchaseDetails();
                    $purchaseDetails->PurchaseCode = $purchaseCodeCode;
                    $purchaseDetails->ItemCode = $singleData['itemCode'];
                    $purchaseDetails->unitPrice = $singleData['unitPrice'];
                    $purchaseDetails->Quantity = $singleData['quantity'];
                    $purchaseDetails->Value = $singleData['itemValue'];
                    $purchaseDetails->save();

                    //Data insert into Stock Batch
                    $existingStockTable = StockBatch::where('ItemCode', $singleData['itemCode'])->where('LocationCode',$singleData['LocationCode'])->first();
                    if($existingStockTable){
                        $existingBatchQty = $existingStockTable->BatchQty;
                        $existingStockValue = $existingStockTable->StockValue;
                        StockBatch::where('ItemCode', $singleData['itemCode'])->where('LocationCode',$singleData['LocationCode'])->update([
                            'ReceiveQty'=>$existingBatchQty + $singleData['quantity'],
                            'BatchQty'=>$existingBatchQty + $singleData['quantity'],
                            'StockValue'=>$existingBatchQty + $singleData['quantity'],
                        ]);
                    }
                    else{
                        $stockBatch= new StockBatch();
                        $stockBatch->ItemCode = $singleData['itemCode'];
                        $stockBatch->LocationCode = $singleData['LocationCode'];
                        $stockBatch->ReceiveQty = $singleData['quantity'];
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

    //Get Existing Info
    public function getPurchaseInfo($purchaseCode){
        $singlePurchaseMaster =  PurchaseMaster::join('PurchaseDetails', 'PurchaseDetails.PurchaseCode', 'PurchaseMaster.PurchaseCode')
            ->join('ItemsCategory','ItemsCategory.CategoryCode','PurchaseMaster.CategoryCode')
            ->join('Items',function ($q) {
                $q->on('Items.ItemCode','PurchaseDetails.ItemCode');
            })
            ->where('PurchaseMaster.PurchaseCode',$purchaseCode)
            ->select(
                'PurchaseMaster.PurchaseCode',
                DB::raw("convert(varchar(10),PurchaseMaster.PurchaseDate,23) as PurchaseDate"),
                'PurchaseMaster.Reference',
                'PurchaseMaster.PurchaseType',
                'PurchaseMaster.Returned',
                'PurchaseMaster.Preparedate',

                'PurchaseDetails.ItemCode',
                'PurchaseDetails.UnitPrice',
                'PurchaseDetails.Quantity',
                'PurchaseDetails.Value',

                'ItemsCategory.CategoryCode',
                'ItemsCategory.CategoryName',
                'ItemsCategory.Active',
                'Items.ItemName'
            )
            ->get();

        return response()->json([
            'status' => 'success',
            'PurchaseInfo' => $singlePurchaseMaster
        ], 200);
    }
    //Update Purchase
    public function update(Request $request){
        if($request->purchaseCode){
            $validator = Validator::make($request->all(), [
                'purchase_date' => 'required',
                'categoryType' => 'required',
                'details' => 'required',
            ]);
            if ($validator->fails()) {
                return response()->json(['message' => $validator->errors()], 400);
            }
            else{
                try {
                    DB::beginTransaction();

                    //Data Insert PurchaseMaster

                    $dataPurchase = PurchaseMaster::where('PurchaseCode',$request->purchaseCode)->first();
                    $dataPurchase->PurchaseDate = $request->purchase_date;
                    $dataPurchase->Reference = $request->reference;
                    $dataPurchase->EditDate = Carbon::now()->format('Y-m-d H:i:s');;
                    $dataPurchase->EditBy = Auth::user()->Id;
                    $dataPurchase->save();

                    foreach ($request->details as $key=>$singleData){
                        $existingPurchaseDetails =  PurchaseDetails::where('PurchaseCode',$request->purchaseCode)
                            ->where('ItemCode',$singleData['itemCode'])->first();

                        //Data update  into Stock Batch
                        $existingStockTable = StockBatch::where('ItemCode',$singleData['itemCode'])
                            ->where('LocationCode',$singleData['LocationCode'])
                            ->first();


                        if($existingStockTable){
                            $existingReceiveQty = $existingStockTable->ReceiveQty;
                            $existingBatchQty = $existingStockTable->BatchQty;
                            $existingStockValue = $existingStockTable->StockValue;

                            if($existingPurchaseDetails['Quantity']>$singleData['quantity']) {
                                $UpdateLessQuantity = $existingPurchaseDetails['Quantity'] - $singleData['quantity'];
                                //Back Existing Product
                                StockBatch::where('ItemCode', $singleData['itemCode'])->where('LocationCode', $singleData['LocationCode'])->update([
                                    'ReceiveQty' => floatval($existingReceiveQty) - floatval($UpdateLessQuantity),
                                    'BatchQty' => floatval($existingBatchQty) - floatval($UpdateLessQuantity),
                                    'StockValue' => floatval($existingBatchQty) - floatval($UpdateLessQuantity),
                                ]);
                            }
                            else{

                                $UpdateGreaterQuantity = $singleData['quantity'] - $existingPurchaseDetails['Quantity']  ;
                                StockBatch::where('ItemCode', $singleData['itemCode'])->where('LocationCode',$singleData['LocationCode'])->update([
                                    'ReceiveQty'=>floatval($existingReceiveQty) + floatval($UpdateGreaterQuantity),
                                    'BatchQty'=>floatval($existingBatchQty) +  floatval($UpdateGreaterQuantity),
                                    'StockValue'=>floatval($existingBatchQty) +  floatval($UpdateGreaterQuantity),
                                ]);

                            }
                        }
                        if($existingPurchaseDetails){
                            $existingPurchaseDetails =  PurchaseDetails::where('PurchaseCode',$request->purchaseCode)
                                ->where('ItemCode',$singleData['itemCode'])->delete();
                          }



                        //Data Insert ProductionDetails
                        $purchaseDetails = new PurchaseDetails();
                        $purchaseDetails->PurchaseCode = $dataPurchase->PurchaseCode;
                        $purchaseDetails->ItemCode = $singleData['item']['ItemCode'];
                        $purchaseDetails->unitPrice = $singleData['unitPrice'];
                        $purchaseDetails->Quantity = $singleData['quantity'];
                        $purchaseDetails->Value = $singleData['itemValue'];
                        $purchaseDetails->save();


                    }
                    DB::commit();
                    return [
                        'status' => 'success',
                        'message' => 'Purchase Updated Successfully'
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

    //Return
     public function returnProducts(Request $request){
         if($request->purchaseCode){
             try {
                 DB::beginTransaction();

                 //Data Insert PurchaseMaster

                 $dataPurchase = PurchaseMaster::where('PurchaseCode',$request->purchaseCode)->first();
                 $dataPurchase->Returned = 'Y';
                 $dataPurchase->EditDate = Carbon::now()->format('Y-m-d H:i:s');;
                 $dataPurchase->EditBy = Auth::user()->Id;
                 $dataPurchase->save();


                 $existingPurchaseDetails =  PurchaseDetails::where('PurchaseCode',$request->purchaseCode)->get();

                 foreach ($existingPurchaseDetails as $key=>$singleData){

                     //Data update  into Stock Batch
                     $existingStockTable = StockBatch::where('ItemCode', $singleData->ItemCode)->where('LocationCode','L0001')->first();
                     if($existingStockTable){
                         $existingBatchQty = $existingStockTable->BatchQty;
                         $existingStockValue = $existingStockTable->StockValue;
                         StockBatch::where('ItemCode', $singleData->ItemCode)->where('LocationCode','L0001')->update([
                             'BatchQty'=>intval($existingBatchQty) - intval($singleData->Quantity),
                             'StockValue'=>intval($existingStockValue) - intval($singleData->Value),
                         ]);
                     }
                 }
                 DB::commit();
                 return [
                     'status' => 'success',
                     'message' => 'Purchase Returned Successfully'
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
