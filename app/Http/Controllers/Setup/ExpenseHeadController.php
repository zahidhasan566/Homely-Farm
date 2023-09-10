<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\ExpenseHead;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Traits\CodeGeneration;

class ExpenseHeadController extends Controller
{
     use CodeGeneration;
   public function index(Request $request){
       $take = $request->take;
       $search = $request->search;
       $expenseHead=  ExpenseHead::where(function ($q) use ($search) {
           $q->where('HeadCode', 'like', '%' . $search . '%');
           $q->orWhere('ExpenseHead', 'like', '%' . $search . '%');
       })
           ->select('HeadCode', 'ExpenseHead','Active')
           ->paginate($take);
       return $expenseHead;
   }

   //Store
    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'Name' => 'required|string',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }
        //Data Insert
        try {
            DB::beginTransaction();
            $expenseHeadCode = $this->generateExpenseHeadCode();
            $expenseHead= new ExpenseHead();
            $expenseHead->HeadCode =$expenseHeadCode;
            $expenseHead->ExpenseHead = $request->Name;
            $expenseHead->Active = $request->status;
            $expenseHead->save();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Expense Head Created Successfully'
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage() . '-' . $exception->getLine()
            ], 500);
        }
    }

    public function getExpenseHeadInfo($headCode){
        $data= ExpenseHead::where('HeadCode', $headCode)->first();
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'Name' => 'required|string',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }
        try {
            DB::beginTransaction();
            $expenseHead = ExpenseHead::where('HeadCode', $request->HeadCode)->first();
            $expenseHead->ExpenseHead = $request->Name;
            $expenseHead->Active = $request->status;
            $expenseHead->save();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Expense Head Updated Successfully'
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], 500);
        }

    }
}
