<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait CodeGeneration
{
    public function generateProductionMasterCode()
    {
        $combinedCode = 'PR'.Carbon::now()->format('y');
        $combinedLength = strlen($combinedCode);
        $maxCode = DB::select(DB::raw("select MAX(ProductionCode) as MaxNo FROM ProductionMaster WHERE LEFT(ProductionCode,'$combinedLength') = '$combinedCode'"));
        $maxCode = $maxCode[0]->MaxNo;
        if ($maxCode === null) {
            $nextCode = $combinedCode.'000001';
        } else {
            $nextCode = substr($maxCode,$combinedLength);
            $nextCodeInc = $nextCode + 1;
            $nextCode = sprintf("%0".strlen($nextCode)."d", $nextCodeInc);
            $nextCode = $combinedCode.$nextCode;
        }
        return $nextCode;
    }
}
