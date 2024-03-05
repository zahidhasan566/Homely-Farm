<?php

namespace App\Http\Controllers\Transfer;

use App\Http\Controllers\Controller;
use App\Models\CategoryLocation;
use App\Models\Items;
use App\Models\ItemsCategory;
use App\Models\Location;
use App\Models\ReceiveDetails;
use App\Models\ReceiveMaster;
use App\Models\StockBatch;
use App\Models\TransferDetails;
use App\Models\TransferMaster;
use App\Traits\CodeGeneration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MedicineTransferController extends Controller
{
    use CodeGeneration;
    public function getSupportingData(){
        return response()->json([
            'status' => 'success',
            'category' => ItemsCategory::where('Expense','Y')->where('Active','Y')->get(),
            'allCategory' => ItemsCategory::select('CategoryCode','CategoryName')->where('Active','Y')->get(),
        ]);
    }

    public function getCategoryWiseItemData(Request $request){

        $location = CategoryLocation::select(
            'CategoryLocation.CategoryCode',
            'CategoryLocation.LocationCode',
            'CategoryLocation.Active',
            'Location.LocationName',
            'Location.Active as LocationStatus',
            'Location.StockTransfer as LocationStockTransfer',
        )->join('Location','Location.LocationCode','CategoryLocation.LocationCode')
            ->where('CategoryLocation.CategoryCode',$request->CategoryCode)
            ->where('CategoryLocation.Active','Y')->get();

        $receiveCategoryItems=[];
        $receiveLocations=[];
        if(($request->receiveCategoryCode)){
            $receiveCategoryItems  = Items::where('CategoryCode',$request->receiveCategoryCode)->get();
            $receiveLocations = CategoryLocation::select(
                'CategoryLocation.CategoryCode',
                'CategoryLocation.LocationCode',
                'CategoryLocation.Active',
                'Location.LocationName',
                'Location.Active as LocationStatus',
                'Location.StockTransfer as LocationStockTransfer',
            )->join('Location','Location.LocationCode','CategoryLocation.LocationCode')
                ->where('CategoryLocation.CategoryCode',$request->receiveCategoryCode)
                ->where('CategoryLocation.Active','Y')->get();
        }

        return response()->json([
            'status' => 'success',
            'items' => Items::where('CategoryCode',$request->CategoryCode)->get(),
            'receiveCategoryItems' => $receiveCategoryItems,
            'locations' => $location,
            'receiveLocations' => $receiveLocations,
        ]);
    }
    public function checkItemWiseStockData(Request $request){
        $categoryCode=  $request->categoryType['CategoryCode'];
        $itemCode=  $request->itemCode;
        $locationCode=  $request->locationCode;
        $stock = DB::select("exec  sp_StockNValue '$categoryCode','$itemCode','$locationCode'");
        //$stock  =  StockBatch::select('BatchQty')->where('ItemCode',$request->itemCode)->where('LocationCode',$request->locationCode)->first();
        return response()->json([
            'stock' => $stock
        ]);
    }

    public function index(Request $request){
        $take = $request->take;
        $search = $request->search;
        // $transferMaster =  TransferMaster::join('TransferDetails', 'TransferDetails.TransferCode', 'TransferMaster.TransferCode')
        //                           ->join('ItemsCategory','ItemsCategory.CategoryCode','TransferMaster.CategoryCode')
        //                           ->join('Items','Items.CategoryCode','ItemsCategory.CategoryCode')
        //                           ->where(function ($q) use ($search) {
        //                             $q->where('TransferMaster.TransferCode', 'like', '%' . $search . '%');
        //                             $q->orWhere('TransferMaster.TransferDate', 'like', '%' . $search . '%');
        //                          })
        //                         ->where('TransferMaster.Returned','!=','Y')
        $transferMaster =  TransferMaster::
        join('ReceiveMaster','ReceiveMaster.TransferCode','TransferMaster.TransferCode')
        ->where(function ($q) use ($search) {
            $q->where('TransferMaster.TransferCode', 'like', '%' . $search . '%');
            $q->orWhere('TransferMaster.TransferDate', 'like', '%' . $search . '%');
        })
            ->where('TransferMaster.Returned','!=','Y')
            ->where('ReceiveMaster.ReceiveType','EXPENSE')
            // ->select(
            //     'ProductionMaster.ProductionCode',
            //     DB::raw("convert(varchar(10),ProductionMaster.ProductionDate,23) as ProductionDate"),
            //     'ProductionMaster.Reference',
            //     'ItemsCategory.CategoryName',
            //     DB::raw("(CASE WHEN ProductionMaster.Returned = 'Y' THEN 'Yes' ELSE 'No' END) AS Returned"),

            //     DB::raw("convert(varchar(10),ProductionMaster.PrepareDate,23) as PrepareDate"),
            //     )
            // ->groupBy(
            //     'TransferMaster.TransferCode',
            //     'TransferMaster.TransferDate',
            //     'TransferMaster.Reference',
            //     'TransferMaster.CategoryCode',
            //     'TransferMaster.Returned',
            //     'TransferMaster.PrepareBy',
            //     'TransferMaster.PrepareDate',
            //     'TransferMaster.EditDate',
            //     'TransferMaster.EditBy'
            //     )
            ->paginate($take);
        return $transferMaster;
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'transfer_date' => 'required',
            'categoryType' => 'required',
            'details' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }
        else{
            try {
                DB::beginTransaction();

                //Data Insert TransferMaster
                $transferCode =  $this->generateTransferMasterCode();

                $transfer = new TransferMaster();
                $transfer->TransferCode = $transferCode;
                $transfer->TransferDate = $request->transfer_date;
                $transfer->Reference = $request->reference;
                $transfer->CategoryCode = $request->categoryType['CategoryCode'];
                $transfer->Returned = 'N';
                $transfer->PrepareDate = Carbon::now()->format('Y-m-d H:i:s');
                $transfer->PrepareBy = Auth::user()->UserId;
                $transfer->EditDate = '';
                $transfer->EditBy = '';
                $transfer->save();

                foreach ($request->details as $key=>$item){
                    $transferDetails = new TransferDetails();
                    $transferDetails->TransferCode = $transferCode;
                    $transferDetails->ItemCode = $item['itemCode'];
                    $transferDetails->LocationCode = $item['LocationFromCode'];
                    $transferDetails->UnitPrice = $item['itemValue'];
                    $transferDetails->Quantity = $item['quantity'];
                    $transferDetails->Value = $item['totalValue'];
                    $transferDetails->save();

                    //Data insert into Stock Batch
                    $checkExistingFromLocation = StockBatch::where('ItemCode', $item['itemCode'])->where('LocationCode',$item['LocationFromCode'])->orderBy('ItemCode','desc')->first();

                    if($checkExistingFromLocation && $checkExistingFromLocation->BatchQty > 0){
                        $negativeStockCheck = $checkExistingFromLocation->BatchQty - $item['quantity'];

                        if($negativeStockCheck >= 0){
                            $existingReceiveQty = $checkExistingFromLocation->ReceiveQty;
                            $existingBatchQty = $checkExistingFromLocation->BatchQty;
                            $existingStockValue = $checkExistingFromLocation->StockValue;
                            StockBatch::where('ItemCode', $item['itemCode'])->where('LocationCode',$item['LocationFromCode'])->update([
                                'BatchQty'=>intval($existingBatchQty) - intval($item['quantity']),
                                'StockValue'=>intval($existingStockValue) - intval($item['totalValue']),
                            ]);
                        }
                        else{
                            return response()->json(['message' => "Product quantity exceed the stock limit!"], 400);
                        }
                    }
                    else{
                        return response()->json(['message' => "Stock unavailable!"], 400);
                    }
                }

                //Data Insert ReceiveMaster
                $receiveCode =  $this->generateReceiveMasterCode();

                $receive = new ReceiveMaster();
                $receive->ReceiveCode = $receiveCode;
                $receive->ReceiveDate = $request->transfer_date;
                $receive->Reference = $request->reference;
                $receive->CategoryCode = $request->receiveCategoryType['CategoryCode'];
                $receive->Returned = 'N';
                $receive->TransferCode = $transferCode;
                $receive->PrepareDate = Carbon::now()->format('Y-m-d H:i:s');;
                $receive->PrepareBy = Auth::user()->UserId;
                $receive->EditDate = '';
                $receive->EditBy = '';
                $receive->save();

                foreach ($request->details as $key=>$item){
                    $receiveDetails = new ReceiveDetails();
                    $receiveDetails->ReceiveCode = $receiveCode;
                    $receiveDetails->ItemCode = $item['receiveItemCode']?$item['receiveItemCode']:"" ;
                    $receiveDetails->TransferLocationCode = $item['LocationFromCode'];
                    $receiveDetails->LocationCode = $item['LocationToCode'];
                    $receiveDetails->UnitPrice = $item['itemValue'];
                    $receiveDetails->Quantity = $item['quantity'];
                    $receiveDetails->Value = $item['totalValue'];
                    $receiveDetails->save();

                    //Data insert into Stock Batch
                    $checkExistingToLocation = StockBatch::where('ItemCode', $item['itemCode'])->where('LocationCode',$item['LocationToCode'])->orderBy('ItemCode','desc')->first();
                    //Check Stock Transfer Status
                    $itemStockTransfer= Location::where('LocationCode',$item['LocationToCode'])->first();
                    $itemStockTransferStatus = $itemStockTransfer->StockTransfer;

                    DB::table('ReceiveMaster')->where('ReceiveCode',$receiveCode) ->update([
                        'ReceiveType'=>'EXPENSE'
                    ]);

//                    if($itemStockTransferStatus==='Y'){
//                        if($checkExistingToLocation){
//                            $existingReceiveQty = $checkExistingToLocation->ReceiveQty;
//                            $existingBatchQty = $checkExistingToLocation->BatchQty;
//                            $existingStockValue = $checkExistingToLocation->StockValue;
//                            DB::table('StockBatch')->where('ItemCode', $item['itemCode'])->where('LocationCode',$item['LocationToCode'])
//                                ->update([
//                                    'ReceiveQty'=>intval($existingReceiveQty) + intval($item['quantity']),
//                                    'BatchQty'=>intval($existingBatchQty) + intval($item['quantity']),
//                                    'StockValue'=>intval($existingStockValue) + intval($item['totalValue']),
//                                ]);
//
//                        }
//                        else{
//                            $stockBatch= new StockBatch();
//                            $stockBatch->ItemCode = $item['itemCode'];
//                            $stockBatch->LocationCode = $item['LocationToCode'];
//                            $stockBatch->ReceiveQty = $item['quantity'];
//                            $stockBatch->BatchQty = $item['quantity'];
//                            $stockBatch->StockValue = $item['quantity'];
//                            $stockBatch->save();
//                        }
//                    }
//                    else{
//                        DB::table('ReceiveMaster')->where('ReceiveCode',$receiveCode) ->update([
//                            'ReceiveType'=>'Other'
//                        ]);
//                    }

                }

                DB::commit();
                return [
                    'status' => 'success',
                    'message' => 'Medicine Transfer has been generated Successfully'
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
