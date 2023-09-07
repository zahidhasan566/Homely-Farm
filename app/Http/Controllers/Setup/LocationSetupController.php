<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\ItemsCategory;
use App\Models\Location;
use App\Traits\CodeGeneration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class LocationSetupController extends Controller
{
    use CodeGeneration;
    public function index(Request $request){
        $take = $request->take;
        $search = $request->search;
        $location=  Location::where(function ($q) use ($search) {
            $q->where('LocationCode', 'like', '%' . $search . '%');
            $q->orWhere('LocationName', 'like', '%' . $search . '%');
        })
            ->select('LocationCode', 'LocationName','Active')
            ->paginate($take);
        return $location;
    }
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'Name' => 'required|string',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }
        //Data Insert
        try {
            DB::beginTransaction();
            $locationCode = $this->generateLocationCode();
            $location= new Location();
            $location->LocationCode =$locationCode;
            $location->LocationName = $request->Name;
            $location->Active = $request->status;
            $location->save();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Location Created Successfully'
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage() . '-' . $exception->getLine()
            ], 500);
        }
    }

    public function getLocationInfo($locationCode){
        $data= Location::where('LocationCode', $locationCode)->first();
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
    //Update Location
    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'Name' => 'required|string',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }
        try {
            DB::beginTransaction();
            $location = Location::where('LocationCode', $request->LocationCode)->first();
            $location->LocationName = $request->Name;
            $location->Active = $request->status;
            $location->save();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Location Updated Successfully'
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], 500);
        }

    }
}
