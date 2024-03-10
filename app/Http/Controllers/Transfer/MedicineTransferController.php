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
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MedicineTransferController extends Controller
{
    use CodeGeneration;

    public function getSupportingData()
    {
        return response()->json([
            'status' => 'success',
            'category' => ItemsCategory::where('Expense', 'Y')->where('Active', 'Y')->get(),
            'allCategory' => ItemsCategory::select('CategoryCode', 'CategoryName')->where('Active', 'Y')->get(),
        ]);
    }

    public function getCategoryWiseItemData(Request $request)
    {

        $location = CategoryLocation::select(
            'CategoryLocation.CategoryCode',
            'CategoryLocation.LocationCode',
            'CategoryLocation.Active',
            'Location.LocationName',
            'Location.Active as LocationStatus',
            'Location.StockTransfer as LocationStockTransfer',
        )->join('Location', 'Location.LocationCode', 'CategoryLocation.LocationCode')
            ->where('CategoryLocation.CategoryCode', $request->CategoryCode)
            ->where('CategoryLocation.Active', 'Y')->get();

        $receiveCategoryItems = [];
        $receiveLocations = [];
        if (($request->receiveCategoryCode)) {
            $receiveCategoryItems = Items::where('CategoryCode', $request->receiveCategoryCode)->get();
            $receiveLocations = CategoryLocation::select(
                'CategoryLocation.CategoryCode',
                'CategoryLocation.LocationCode',
                'CategoryLocation.Active',
                'Location.LocationName',
                'Location.Active as LocationStatus',
                'Location.StockTransfer as LocationStockTransfer',
            )->join('Location', 'Location.LocationCode', 'CategoryLocation.LocationCode')
                ->where('CategoryLocation.CategoryCode', $request->receiveCategoryCode)
                ->where('CategoryLocation.Active', 'Y')->get();
        }

        return response()->json([
            'status' => 'success',
            'items' => Items::where('CategoryCode', $request->CategoryCode)->get(),
            'receiveCategoryItems' => $receiveCategoryItems,
            'locations' => $location,
            'receiveLocations' => $receiveLocations,
        ]);
    }

    public function checkItemWiseStockData(Request $request)
    {
        if(empty($request->categoryType[0]['CategoryCode'] )){
            $categoryCode = $request->categoryType['CategoryCode'];
        }
        else{
            $categoryCode = $request->categoryType[0]['CategoryCode'];

        }
        $itemCode = $request->itemCode;
        $locationCode = $request->locationCode;
        $stock = DB::select("exec  sp_StockNValue '$categoryCode','$itemCode','$locationCode'");
        //$stock  =  StockBatch::select('BatchQty')->where('ItemCode',$request->itemCode)->where('LocationCode',$request->locationCode)->first();
        return response()->json([
            'stock' => $stock
        ]);
    }

    public function index(Request $request)
    {
        $take = $request->take;
        $search = $request->search;
        $transferMaster = TransferMaster::
        select(
            'TransferMaster.TransferCode',
            'TransferMaster.TransferDate',
            'TransferMaster.Reference',
            'TransferMaster.CategoryCode',
            'TransferMaster.Returned',
            'ReceiveMaster.Preparedate as Prepared Date',

        )
            ->join('ReceiveMaster', 'ReceiveMaster.TransferCode', 'TransferMaster.TransferCode')
            ->where(function ($q) use ($search) {
                $q->where('TransferMaster.TransferCode', 'like', '%' . $search . '%');
                $q->orWhere('TransferMaster.TransferDate', 'like', '%' . $search . '%');
            })
            ->where('TransferMaster.Returned', '!=', 'Y')
            ->where('ReceiveMaster.ReceiveType', 'EXPENSE')
            ->paginate($take);
        return $transferMaster;
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'transfer_date' => 'required',
            'categoryType' => 'required',
            'details' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        } else {
            try {
                DB::beginTransaction();

                //Data Insert TransferMaster
                $transferCode = $this->generateTransferMasterCode();

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

                foreach ($request->details as $key => $item) {
                    $transferDetails = new TransferDetails();
                    $transferDetails->TransferCode = $transferCode;
                    $transferDetails->ItemCode = $item['itemCode'];
                    $transferDetails->LocationCode = $item['LocationFromCode'];
                    $transferDetails->UnitPrice = $item['itemValue'];
                    $transferDetails->Quantity = $item['quantity'];
                    $transferDetails->Value = $item['totalValue'];
                    $transferDetails->save();

                    //Data insert into Stock Batch
                    $checkExistingFromLocation = StockBatch::where('ItemCode', $item['itemCode'])->where('LocationCode', $item['LocationFromCode'])->orderBy('ItemCode', 'desc')->first();

                    if ($checkExistingFromLocation && $checkExistingFromLocation->BatchQty > 0) {
                        $negativeStockCheck = $checkExistingFromLocation->BatchQty - $item['quantity'];

                        if ($negativeStockCheck >= 0) {
                            $existingReceiveQty = $checkExistingFromLocation->ReceiveQty;
                            $existingBatchQty = $checkExistingFromLocation->BatchQty;
                            $existingStockValue = $checkExistingFromLocation->StockValue;
                            StockBatch::where('ItemCode', $item['itemCode'])->where('LocationCode', $item['LocationFromCode'])->update([
                                'BatchQty' => intval($existingBatchQty) - intval($item['quantity']),
                                'StockValue' => intval($existingStockValue) - intval($item['totalValue']),
                            ]);
                        } else {
                            return response()->json(['message' => "Product quantity exceed the stock limit!"], 400);
                        }
                    } else {
                        return response()->json(['message' => "Stock unavailable!"], 400);
                    }
                }

                //Data Insert ReceiveMaster
                $receiveCode = $this->generateReceiveMasterCode();

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

                foreach ($request->details as $key => $item) {
                    $receiveDetails = new ReceiveDetails();
                    $receiveDetails->ReceiveCode = $receiveCode;
                    $receiveDetails->ItemCode = $item['itemCode'] ? $item['itemCode'] : "";
                    $receiveDetails->TransferLocationCode = $item['LocationFromCode'];
                    $receiveDetails->LocationCode = $item['LocationToCode'];
                    $receiveDetails->UnitPrice = $item['itemValue'];
                    $receiveDetails->Quantity = $item['quantity'];
                    $receiveDetails->Value = $item['totalValue'];
                    $receiveDetails->ReceiveItemCode = $item['receiveItemCode'] ? $item['receiveItemCode'] : "";
                    $receiveDetails->save();

                    //Data insert into Stock Batch
                    $checkExistingToLocation = StockBatch::where('ItemCode', $item['itemCode'])->where('LocationCode', $item['LocationToCode'])->orderBy('ItemCode', 'desc')->first();
                    //Check Stock Transfer Status
                    $itemStockTransfer = Location::where('LocationCode', $item['LocationToCode'])->first();
                    $itemStockTransferStatus = $itemStockTransfer->StockTransfer;

                    DB::table('ReceiveMaster')->where('ReceiveCode', $receiveCode)->update([
                        'ReceiveType' => 'EXPENSE'
                    ]);

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

    public function getExistingMedicineTransferInfo($transferCode)
    {
        $existingTransferInfo = TransferMaster::with('Category','TransferDetails.TransferItems','ReceiveMaster.Category','ReceiveMaster.ReceiveDetails')->
        select(
            'TransferMaster.TransferCode',
            'TransferMaster.TransferDate',
            'TransferMaster.Reference',
            'TransferMaster.CategoryCode',
            'TransferMaster.Returned',
            'StockBatch.BatchQty'
        )
            ->join('TransferDetails','TransferDetails.TransferCode','TransferMaster.TransferCode')
            ->join('StockBatch',function($q) {
                $q->on('StockBatch.ItemCode','TransferDetails.ItemCode');
                $q->on('StockBatch.LocationCode','TransferDetails.LocationCode');
            })
            ->where('TransferMaster.TransferCode', $transferCode)
            ->get();

//        $existingTransferInfo = DB::table('TransferMaster', 'tm')->
//        select(
//            'tm.TransferCode',
//            'tm.TransferDate',
//            'tm.Reference',
//            'tm.CategoryCode',
//            'tm.Returned',
//            'td.ItemCode',
//            'td.LocationCode',
//            'td.UnitPrice',
//            'td.Quantity',
//            'td.Value',
//            'rm.CategoryCode as ReceiveCategoryCode',
//            'rm.ReceiveType',
//            'rd.ItemCode as ReceiveItemCode',
//            'rd.TransferLocationCode as fromLocation',
//            'rd.LocationCode as toLocation',
//            'ic.CategoryName',
//            'ic2.CategoryName as  ReceiveCategoryName'
//        )
//            ->join('TransferDetails as td', 'td.TransferCode', 'tm.TransferCode')
//            ->join('ReceiveMaster as rm', 'rm.TransferCode','tm.TransferCode')
//            ->join('ReceiveDetails as rd','rd.ReceiveCode','rm.ReceiveCode')
//            ->join('ItemsCategory as  ic', 'ic.CategoryCode', 'tm.CategoryCode')
//            ->join('ItemsCategory as ic2', 'ic2.CategoryCode', 'rm.CategoryCode')
//            ->where('tm.TransferCode', $transferCode)
//            ->groupBy('tm.TransferCode',
//                'tm.TransferDate',
//                'tm.Reference',
//                'tm.CategoryCode',
//                'tm.Returned',
//                'td.ItemCode',
//                'td.LocationCode',
//                'td.UnitPrice',
//                'td.Quantity',
//                'td.Value',
//                'rm.CategoryCode',
//                'rm.ReceiveType',
//                'rd.ItemCode',
//                'rd.TransferLocationCode',
//                'rd.LocationCode',
//                'ic.CategoryName',
//                'ic2.CategoryName'
//            )
//            ->get();

        return response()->json([
            'existingTransferInfo' => $existingTransferInfo
        ]);
    }

    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'transfer_date' => 'required',
            'categoryType' => 'required',
            'details' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        } else {
            try {
                DB::beginTransaction();

                //Data Insert TransferMaster
                $transfer = TransferMaster::where('TransferCode',$request->transferCode)->first();
                $transfer->TransferDate = $request->transfer_date;
                $transfer->Reference = $request->reference;
                $transfer->CategoryCode =  $request->categoryType[0]['CategoryCode'];
                $transfer->Returned = 'N';
                $transfer->PrepareDate = Carbon::now()->format('Y-m-d H:i:s');
                $transfer->PrepareBy = Auth::user()->UserId;
                $transfer->EditDate = Carbon::now()->format('Y-m-d H:i:s');
                $transfer->EditBy =Auth::user()->UserId;
                $transfer->save();


                //Stock Return
                $transferDetails  = TransferDetails::where('TransferCode',$request->transferCode);
                foreach ($transferDetails as $key => $item) {
                    $checkExistingStock = StockBatch::where('ItemCode', $item['itemCode'])->where('LocationCode', $item['LocationCode'])->orderBy('ItemCode', 'desc')->first();
                    StockBatch::where('ItemCode', $item['itemCode'])->where('LocationCode', $item['LocationCode'])->update([
                        'BatchQty' => intval($checkExistingStock->BatchQty) + intval($item['quantity']),
                        'StockValue' => intval($checkExistingStock->BatchQty) + intval($item['UnitPrice']* $item['Quantity']),
                    ]);
                }
                //Delete Existing
                TransferDetails::where('TransferCode',$request->transferCode)->delete();


                foreach ($request->details as $key => $item) {
                    $transferDetails = new TransferDetails();
                    $transferDetails->TransferCode = $request->transferCode;
                    $transferDetails->ItemCode = $item['itemCode'];
                    $transferDetails->LocationCode = $item['LocationFromCode'];
                    $transferDetails->UnitPrice = $item['itemValue'];
                    $transferDetails->Quantity = $item['quantity'];
                    $transferDetails->Value = $item['totalValue'];
                    $transferDetails->save();

                    //Data insert into Stock Batch
                    $checkExistingFromLocation = StockBatch::where('ItemCode', $item['itemCode'])->where('LocationCode', $item['LocationFromCode'])->orderBy('ItemCode', 'desc')->first();

                    if ($checkExistingFromLocation && $checkExistingFromLocation->BatchQty > 0) {
                        $negativeStockCheck = $checkExistingFromLocation->BatchQty - $item['quantity'];

                        if ($negativeStockCheck >= 0) {
                            $existingReceiveQty = $checkExistingFromLocation->ReceiveQty;
                            $existingBatchQty = $checkExistingFromLocation->BatchQty;
                            $existingStockValue = $checkExistingFromLocation->StockValue;
                            StockBatch::where('ItemCode', $item['itemCode'])->where('LocationCode', $item['LocationFromCode'])->update([
                                'BatchQty' => intval($existingBatchQty) - intval($item['quantity']),
                                'StockValue' => intval($existingStockValue) - intval($item['totalValue']),
                            ]);
                        } else {
                            return response()->json(['message' => "Product quantity exceed the stock limit!"], 400);
                        }
                    } else {
                        return response()->json(['message' => "Stock unavailable!"], 400);
                    }
                }

                $existingReceive = ReceiveMaster::select('ReceiveCode')->where('TransferCode',$request->transferCode)->first();
                //Delete Existing
                 ReceiveDetails::where('ReceiveCode',$existingReceive->ReceiveCode)->delete();

                //Data Insert ReceiveMaster
                $receive = ReceiveMaster::where('ReceiveCode', $existingReceive->ReceiveCode)->first();
                $receive->ReceiveDate = $request->transfer_date;
                $receive->Reference = $request->reference;
                $receive->CategoryCode = $request->receiveCategoryType[0]['CategoryCode'];
                $receive->Returned = 'N';
                $receive->TransferCode = $request->transferCode;
                $receive->PrepareDate = Carbon::now()->format('Y-m-d H:i:s');;
                $receive->PrepareBy = Auth::user()->UserId;
                $receive->EditDate = '';
                $receive->EditBy = '';
                $receive->save();


                foreach ($request->details as $key => $item) {
                    $receiveDetails = new ReceiveDetails();
                    $receiveDetails->ReceiveCode = $existingReceive->ReceiveCode;
                    $receiveDetails->ItemCode = $item['itemCode'] ? $item['itemCode'] : "";
                    $receiveDetails->TransferLocationCode = $item['LocationFromCode'];
                    $receiveDetails->LocationCode = $item['LocationToCode'];
                    $receiveDetails->UnitPrice = $item['itemValue'];
                    $receiveDetails->Quantity = $item['quantity'];
                    $receiveDetails->Value = $item['totalValue'];
                    $receiveDetails->ReceiveItemCode = $item['receiveItemCode'] ? $item['receiveItemCode'] : "";
                    $receiveDetails->save();

                    //Data insert into Stock Batch
                    $checkExistingToLocation = StockBatch::where('ItemCode', $item['itemCode'])->where('LocationCode', $item['LocationToCode'])->orderBy('ItemCode', 'desc')->first();
                    //Check Stock Transfer Status
                    $itemStockTransfer = Location::where('LocationCode', $item['LocationToCode'])->first();
                    $itemStockTransferStatus = $itemStockTransfer->StockTransfer;

                    DB::table('ReceiveMaster')->where('ReceiveCode', $existingReceive->ReceiveCode)->update([
                        'ReceiveType' => 'EXPENSE'
                    ]);

                }

                DB::commit();
                return [
                    'status' => 'success',
                    'message' => 'Medicine Transfer has been updated Successfully'
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
