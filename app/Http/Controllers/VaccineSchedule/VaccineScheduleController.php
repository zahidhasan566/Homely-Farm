<?php

namespace App\Http\Controllers\VaccineSchedule;

use App\Http\Controllers\Controller;
use App\Models\VaccineSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\CodeGeneration;


class VaccineScheduleController extends Controller
{
    //
    use CodeGeneration;
    public function index(Request $request){
        $take = $request->take;
        $search = $request->search;
        $location=  VaccineSchedule::join('ItemsCategory','ItemsCategory.CategoryCode',
                            'VaccineSchedule.CategoryCode', )
                        ->join('Location','Location.LocationCode','VaccineSchedule.LocationCode')
        ->where(function ($q) use ($search) {
            $q->where('ItemsCategory.CategoryName', 'like', '%' . $search . '%');
            $q->orWhere('Location.LocationName', 'like', '%' . $search . '%');
        })
            ->select('ScheduleCode','ScheduleDate','VaccineName', 'UnitPrice',
                'ItemsCategory.CategoryName',
                'Location.LocationName', 'NextScheduleDate', 'PrepareDate',
                'PrepareBy', 'VaccineSchedule.CategoryCode', 'VaccineSchedule.LocationCode')
            ->paginate($take);
        return $location;
    }

    public function doStore(Request $request){
        //dd($request->all());
        $validator = Validator::make($request->all(), [
            'ScheduleDate' => 'required|date',
            'VaccineName' => 'required',
            'UnitPrice' => 'required|numeric',
            'CategoryCode' => 'required|string',
            'LocationCode' => 'required|string'
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }
        //Data Insert
        try {
            DB::beginTransaction();
            $VaccineScheduleData= new VaccineSchedule();
            $VaccineScheduleData->ScheduleDate = $request->ScheduleDate;
            $VaccineScheduleData->VaccineName = $request->VaccineName;
            $VaccineScheduleData->UnitPrice = $request->UnitPrice;
            $VaccineScheduleData->CategoryCode = $request->CategoryCode;
            $VaccineScheduleData->LocationCode = $request->LocationCode;
            $VaccineScheduleData->NextScheduleDate = $request->NextScheduleDate;
            if($request->ActionType == 'add'){
                $VaccineScheduleData->ScheduleCode = $this->generateVaccineScheduleCode();
                $VaccineScheduleData->PrepareDate = Carbon::now();
                $VaccineScheduleData->PrepareBy = Auth::user()->UserId;
            }elseif($request->ActionType = 'edit'){
                $VaccineScheduleData->EditDate = Carbon::now();
                $VaccineScheduleData->EditBy = Auth::user()->UserId;
            }
            if($request->ActionType == 'add'){
                $VaccineScheduleData->save();
                DB::commit();
            }elseif($request->ActionType = 'edit'){
                VaccineSchedule::where('ScheduleCode', $request->ScheduleCode)->update([
                    'ScheduleDate'=>$request->ScheduleDate,
                    'VaccineName'=>$request->VaccineName,
                    'UnitPrice'=>$request->UnitPrice,
                    'CategoryCode'=>$request->CategoryCode,
                    'LocationCode'=>$request->LocationCode,
                    'NextScheduleDate'=>$request->NextScheduleDate,
                ]);
            }
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
}
