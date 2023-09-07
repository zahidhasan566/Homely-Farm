<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\ItemsCategory;
use App\Traits\CodeGeneration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CategorySetupController extends Controller
{
    use CodeGeneration;
    public function index(Request $request){
        $take = $request->take;
        $search = $request->search;
        $category=  ItemsCategory::where(function ($q) use ($search) {
            $q->where('CategoryCode', 'like', '%' . $search . '%');
            $q->orWhere('CategoryName', 'like', '%' . $search . '%');
        })
            ->select('CategoryCode', 'CategoryName','Active')
            ->paginate($take);
        return $category;
    }
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
            $categoryCode = $this->generateCategoryCode();
            $category = new ItemsCategory();
            $category->CategoryCode =$categoryCode;
            $category->CategoryName = $request->Name;
            $category->Active = $request->status;
            $category->save();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Category Created Successfully'
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage() . '-' . $exception->getLine()
            ], 500);
        }
    }

    //Get Existing info
    public function getCategoryInfo($categoryCode){
        $category= ItemsCategory::where('CategoryCode', $categoryCode)->first();
        return response()->json([
            'status' => 'success',
            'category' => $category,
        ]);
    }

    //Update Category
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
            $category = ItemsCategory::where('CategoryCode', $request->CategoryCode)->first();
            $category->CategoryName = $request->Name;
            $category->Active = $request->status;
            $category->save();
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Category Updated Successfully'
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], 500);
        }

    }
}
