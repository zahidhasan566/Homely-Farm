<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\CategoryLocation;
use App\Models\ItemsCategory;
use App\Models\Location;
use App\Traits\CodeGeneration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategoryLocationSetupController extends Controller
{
    use CodeGeneration;
    public function index(Request $request){
        $take = $request->take;
        $search = $request->search;
        $location=  CategoryLocation::join('ItemsCategory','ItemsCategory.CategoryCode','CategoryLocation.CategoryCode')
                        ->join('Location','Location.LocationCode','CategoryLocation.LocationCode')
        ->where(function ($q) use ($search) {
            $q->where('ItemsCategory.CategoryName', 'like', '%' . $search . '%');
            $q->orWhere('Location.LocationName', 'like', '%' . $search . '%');
        })
            ->select('CategoryLocation.CategoryCode','Location.LocationCode','ItemsCategory.CategoryName', 'Location.LocationName','CategoryLocation.Active')
            ->paginate($take);
        return $location;
    }
    public function getSupportingData(){
        $category = ItemsCategory::all();
        $location= Location::all();
        return response()->json([
            'category' => $category,
            'location' => $location,
        ]);
    }
    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'CategoryCode' => 'required',
            'LocationCode' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }
        //Data Insert
        try {
            DB::beginTransaction();
            $location= new CategoryLocation();
            $location->CategoryCode =$request->CategoryCode;
            $location->LocationCode = $request->LocationCode;
            $location->Active = $request->status;
            $location->save();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Category Location Created Successfully'
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage() . '-' . $exception->getLine()
            ], 500);
        }
    }
    public function getCategoryLocationInfo($categoryCode,$locationCode){
        $data= CategoryLocation::select('CategoryCode','LocationCode','Active')->where('CategoryCode',$categoryCode)->where('LocationCode', $locationCode)->first();
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'CategoryCode' => 'required',
            'LocationCode' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }
        try {
            DB::beginTransaction();
            $location = CategoryLocation::where('CategoryCode',$request->CategoryCode)->where('LocationCode', $request->LocationCode)->update([
                'CategoryCode'=>$request->CategoryCode,
                'LocationCode'=>$request->LocationCode,
                'Active'=>$request->status,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Category Location Updated Successfully'
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], 500);
        }

    }


}
