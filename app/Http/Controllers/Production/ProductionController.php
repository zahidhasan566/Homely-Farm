<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Items;
use App\Models\ItemsCategory;
use App\Models\Location;
use App\Models\ProductionDetails;
use App\Models\ProductionMaster;
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
            'category' => ItemsCategory::all(),
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

            ->orderBy('ProductionMaster.PrepareDate', 'desc')
            ->select(
                'ProductionMaster.ProductionCode',
                'ProductionMaster.ProductionDate',
                'ProductionMaster.Reference',
                'ItemsCategory.CategoryName',
                'ProductionMaster.Returned',
                'ProductionMaster.PrepareDate',
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
        return response()->json([
            'status' => 'success',
            'items' => Items::where('CategoryCode',$request->CategoryCode)->get(),
            'locations' => Location::all(),
        ]);
    }

    //Add Data
    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'production_date' => 'required',
            'reference' => 'required',
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
                $dataProduction->PrepareBy = Auth::user()->Id;
                $dataProduction->save();

                foreach ($request->details as $key=>$singleData){

                    //Data Insert ProductionDetails
                    $productionDetails = new ProductionDetails();
                    $productionDetails->ProductionCode = $productionCode;
                    $productionDetails->ItemCode = $singleData['item']['ItemCode'];
                    $productionDetails->LocationCode = $singleData['location']['LocationCode'];
                    $productionDetails->Quantity = $singleData['quantity'];
                    $productionDetails->Value = $singleData['itemValue'];
                    $productionDetails->save();
                }
                DB::commit();
                return [
                    'status' => 'success',
                    'message' => 'Production Created Successfully'
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
