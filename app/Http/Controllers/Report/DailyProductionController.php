<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\ItemsCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyProductionController extends Controller
{
    public function index(Request $request){

        $search = $request->search;
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

        $dailyProduction = DB::select("exec sp_DailyProduction '$dateFrom','$dateTo','$catergoryCode','$search'");

        return response()->json([
            'data' => $dailyProduction,
        ]);

        //dd($dailyProduction);
    }

    public function getSupportingData(){
        $category = ItemsCategory::select('CategoryName as text', 'CategoryCode as value')->where('Active','Y')->get();
        return response()->json([
            'category' => $category,
        ]);
    }


}
