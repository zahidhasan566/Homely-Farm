<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailySalesController extends Controller
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
        //  dd($dateFrom,$dateTo);

        $catergoryCode = '%';
        $dailyProduction = DB::select("exec sp_DailySales '$dateFrom','$dateTo','$catergoryCode'");

        return response()->json([
            'data' => $dailyProduction,
        ]);

        //dd($dailyProduction);
    }
}
