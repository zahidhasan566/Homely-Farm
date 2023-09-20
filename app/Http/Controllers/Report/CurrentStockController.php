<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\StockBatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CurrentStockController extends Controller
{
    public function index(Request $request){
        $search = $request->search;
        $result =  DB::select("exec sp_CurrentStock ");

        return response()->json([
            'data' => $result
        ]);
    }
}
