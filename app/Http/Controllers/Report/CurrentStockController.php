<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\StockBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CurrentStockController extends Controller
{
    public function index(Request $request){

        $take = $request->take;
        $search = $request->search;

        $stockBatch =  StockBatch::join('Items', 'Items.ItemCode', 'StockBatch.ItemCode')
            ->join('Location', 'Location.LocationCode', 'StockBatch.LocationCode')
            ->where(function ($q) use ($search) {
                $q->where('Items.ItemName', 'like', '%' . $search . '%');
                $q->orWhere('Location.LocationName', 'like', '%' . $search . '%');
            })
            ->select('Items.ItemName as Item', 'Location.LocationName as Location','StockBatch.BatchQty as CurrentStock',
                //DB::raw("CASE WHEN ReceiveQty IS NOT NULL AND BatchQty IS NOT NULL THEN (ReceiveQty-BatchQty) end as CurrentStock")
            );
//        if(!empty($request->filters[0]['value'])){
//            $first = $request->filters[0]['value'][0];
//            $second = $request->filters[0]['value'][1];
//
//            $start_date = date("Y-m-d", strtotime($first));
//            $end_date = date("Y-m-d", strtotime($second));
//
//            $stockBatch =  $stockBatch->whereBetween(DB::raw("CONVERT(DATE,Entries.CreatedAt)"), [$start_date, $end_date]);
//        }
        return $stockBatch->paginate($take);

        //dd($dateFrom,$dateTo);
    }
}
