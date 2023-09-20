<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\ItemsCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailySalesController extends Controller
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

        if(!empty($request->filters[2]['value'])){
            $paid  = $request->filters[2]['value'];
        }
        else{
            $paid = '%';
        }

        $dailyProduction = DB::select("exec sp_DailySales '$dateFrom','$dateTo','$catergoryCode','$search','$paidgit'");

        return response()->json([
            'data' => $dailyProduction,
        ]);

    }
    public function getSupportingData(){
        $category = ItemsCategory::select('CategoryName as text', 'CategoryCode as value')->where('Active','Y')->get();
        return response()->json([
            'category' => $category,
        ]);
    }
}
