<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\StockBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LocationWisePLController extends Controller
{
    public function index(Request $request){
        if(!empty($request->filters[0]['value'])){
            $first = $request->filters[0]['value'][0];
            $second = $request->filters[0]['value'][1];

            $dateFrom = date("Y-m-d", strtotime($first));
            $dateTo = date("Y-m-d", strtotime($second));
        }
        else{
            $dateFrom = Carbon::now()->format('Y-m-d');;
            $dateTo =  Carbon::now()->format('Y-m-d');;
        }
        if(!empty($request->filters[1]['value'])){
            $catergoryCode  = $request->filters[1]['value'];
        }
        else{
            $catergoryCode = '%';
        }

        $result =  DB::select("exec sp_LocationWisePL '$dateFrom','$dateTo','$catergoryCode' ");

        return response()->json([
            'data' => $result
        ]);
    }
}
