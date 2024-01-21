<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use App\Traits\GetMultipleProcedureResult;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    use GetMultipleProcedureResult;
    public function index(){
        $result = $this->getPdoResult();
        $stock = DB::select("exec  sp_CurrentStockDashboard");
        return response()->json([
            'data' => $result,
            'stock' => $stock,
        ]);
    }

}
