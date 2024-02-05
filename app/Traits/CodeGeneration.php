<?php

namespace App\Traits;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait CodeGeneration
{
    //Production Master Code Generator
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

    //PurchaseMaster  Code Generator
    public function generatePurchaseMasterCode()
    {
        $combinedCode = 'PC'.Carbon::now()->format('y');
        $combinedLength = strlen($combinedCode);
        $maxCode = DB::select(DB::raw("select MAX(PurchaseCode) as MaxNo FROM PurchaseMaster WHERE LEFT(PurchaseCode,'$combinedLength') = '$combinedCode'"));
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
    //Sales Code Generator
    public function generateSalesMasterCode()
    {
        $combinedCode = 'SL'.Carbon::now()->format('y');
        $combinedLength = strlen($combinedCode);
        $maxCode = DB::select(DB::raw("select MAX(SalesCode) as MaxNo FROM SalesMaster WHERE LEFT(SalesCode,'$combinedLength') = '$combinedCode'"));
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
    public function generateCustomerCode()
    {
        $combinedCode = 'CM';
        $combinedLength = strlen($combinedCode);
        $maxCode = DB::select(DB::raw("select MAX(CustomerCode) as MaxNo FROM Customer WHERE LEFT(CustomerCode,'$combinedLength') = '$combinedCode'"));
        $maxCode = $maxCode[0]->MaxNo;
        if ($maxCode === null) {
            $nextCode = $combinedCode.'001';
        } else {
            $nextCode = substr($maxCode,$combinedLength);
            $nextCodeInc = $nextCode + 1;
            $nextCode = sprintf("%0".strlen($nextCode)."d", $nextCodeInc);
            $nextCode = $combinedCode.$nextCode;
        }
        return $nextCode;
    }
    public function generateCategoryCode()
    {
        $combinedCode = 'CC';
        $combinedLength = strlen($combinedCode);
        $maxCode = DB::select(DB::raw("select MAX(CategoryCode) as MaxNo FROM ItemsCategory WHERE LEFT(CategoryCode,'$combinedLength') = '$combinedCode'"));
        $maxCode = $maxCode[0]->MaxNo;
        if ($maxCode === null) {
            $nextCode = $combinedCode.'001';
        } else {
            $nextCode = substr($maxCode,$combinedLength);
            $nextCodeInc = $nextCode + 1;
            $nextCode = sprintf("%0".strlen($nextCode)."d", $nextCodeInc);
            $nextCode = $combinedCode.$nextCode;
        }
        return $nextCode;
    }
    public function generateItemCode()
    {
        $combinedCode = 'IT';
        $combinedLength = strlen($combinedCode);
        $maxCode = DB::select(DB::raw("select MAX(ItemCode) as MaxNo FROM Items WHERE LEFT(ItemCode,'$combinedLength') = '$combinedCode'"));
        $maxCode = $maxCode[0]->MaxNo;
        if ($maxCode === null) {
            $nextCode = $combinedCode.'001';
        } else {
            $nextCode = substr($maxCode,$combinedLength);
            $nextCodeInc = $nextCode + 1;
            $nextCode = sprintf("%0".strlen($nextCode)."d", $nextCodeInc);
            $nextCode = $combinedCode.$nextCode;
        }
        return $nextCode;
    }
    public function generateLocationCode()
    {
        $combinedCode = 'L';
        $combinedLength = strlen($combinedCode);
        $maxCode = DB::select(DB::raw("select MAX(LocationCode) as MaxNo FROM Location WHERE LEFT(LocationCode,'$combinedLength') = '$combinedCode'"));
        $maxCode = $maxCode[0]->MaxNo;
        if ($maxCode === null) {
            $nextCode = $combinedCode.'001';
        } else {
            $nextCode = substr($maxCode,$combinedLength);
            $nextCodeInc = $nextCode + 1;
            $nextCode = sprintf("%0".strlen($nextCode)."d", $nextCodeInc);
            $nextCode = $combinedCode.$nextCode;
        }
        return $nextCode;
    }
    public function generateExpenseHeadCode()
    {
        $combinedCode = 'E';
        $combinedLength = strlen($combinedCode);
        $maxCode = DB::select(DB::raw("select MAX(HeadCode) as MaxNo FROM ExpenseHead WHERE LEFT(HeadCode,'$combinedLength') = '$combinedCode'"));
        $maxCode = $maxCode[0]->MaxNo;
        if ($maxCode === null) {
            $nextCode = $combinedCode.'0001';
        } else {
            $nextCode = substr($maxCode,$combinedLength);
            $nextCodeInc = $nextCode + 1;
            $nextCode = sprintf("%0".strlen($nextCode)."d", $nextCodeInc);
            $nextCode = $combinedCode.$nextCode;
        }
        return $nextCode;
    }
    // public function generateExpenseCode()
    // {
    //     $combinedCode = 'EX';
    //     $combinedLength = strlen($combinedCode);
    //     $maxCode = DB::select(DB::raw("select MAX(ExpenseCode) as MaxNo FROM ExpenseMaster WHERE LEFT(ExpenseCode,'$combinedLength') = '$combinedCode'"));
    //     $maxCode = $maxCode[0]->MaxNo;
    //     if ($maxCode === null) {
    //         $nextCode = $combinedCode.'0001';
    //     } else {
    //         $nextCode = substr($maxCode,$combinedLength);
    //         $nextCodeInc = $nextCode + 1;
    //         $nextCode = sprintf("%0".strlen($nextCode)."d", $nextCodeInc);
    //         $nextCode = $combinedCode.$nextCode;
    //     }
    //     return $nextCode;
    // }

    public function generateExpenseCode()
    {
        $combinedCode = 'EX'.Carbon::now()->format('y');
        $combinedLength = strlen($combinedCode);
        $maxCode = DB::select(DB::raw("select MAX(ExpenseCode) as MaxNo FROM ExpenseMaster
            WHERE LEFT(ExpenseCode,'$combinedLength') = '$combinedCode'"));
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

    public function generateVaccineScheduleCode()
    {
        $combinedCode = 'VC'.Carbon::now()->format('y');
        $combinedLength = strlen($combinedCode);
        $maxCode = DB::select(DB::raw("select MAX(ScheduleCode) as MaxNo FROM VaccineSchedule
            WHERE LEFT(ScheduleCode,'$combinedLength') = '$combinedCode'"));
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

    public function generateTransferMasterCode()
    {
        $combinedCode = 'TR'.Carbon::now()->format('y');
        $combinedLength = strlen($combinedCode);
        $maxCode = DB::select(DB::raw("select MAX(TransferCode) as MaxNo FROM TransferMaster WHERE LEFT(TransferCode,'$combinedLength') = '$combinedCode'"));
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

    public function generateReceiveMasterCode()
    {
        $combinedCode = 'RR'.Carbon::now()->format('y');
        $combinedLength = strlen($combinedCode);
        $maxCode = DB::select(DB::raw("select MAX(ReceiveCode) as MaxNo FROM ReceiveMaster WHERE LEFT(ReceiveCode,'$combinedLength') = '$combinedCode'"));
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
