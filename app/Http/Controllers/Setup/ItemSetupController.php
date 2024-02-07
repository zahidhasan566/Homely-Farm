<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\CategoryLocation;
use App\Models\Items;
use App\Models\ItemsCategory;
use App\Models\Location;
use App\Traits\CodeGeneration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ItemSetupController extends Controller
{
    use CodeGeneration;
    public function index(Request $request){
        $take = $request->take;
        $search = $request->search;
        $items=  Items::join('ItemsCategory','ItemsCategory.CategoryCode','Items.CategoryCode')
            ->where(function ($q) use ($search) {
                $q->where('ItemsCategory.CategoryName', 'like', '%' . $search . '%');
            })
            ->select('Items.ItemCode','ItemsCategory.CategoryName','Items.ItemName','Items.UOM', 'Items.Description','Items.Active')
            ->paginate($take);
        return $items;
    }

    public function getSupportingData(){
        $category = ItemsCategory::all();
        return response()->json([
            'category' => $category
        ]);
    }
    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'CategoryCode' => 'required',
            'itemName' => 'required',
            'uom' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }
        //Data Insert
        try {
            DB::beginTransaction();
            $item= new Items();
            $itemCode = $this->generateItemCode();
            $item->ItemCode =$itemCode;
            $item->ItemName = $request->itemName;
            $item->UOM = $request->uom;
            $item->Description = $request->description;
            $item->CategoryCode =$request->CategoryCode;
            $item->Active = $request->status;
            $item->save();
            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Item Created Successfully'
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage() . '-' . $exception->getLine()
            ], 500);
        }
    }

    public function getItemInfo($itemCode){
        $data= Items::select('ItemCode','ItemName','UOM','Description','CategoryCode','Active')->where('ItemCode',$itemCode)->first();
        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'CategoryCode' => 'required',
            'itemName' => 'required',
            'uom' => 'required',
            'status' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()], 400);
        }
        try {
            DB::beginTransaction();
            $item = Items::where('ItemCode',$request->itemCode)->update([
                'ItemName'=>$request->itemName,
                'UOM'=>$request->uom,
                'Description'=>$request->description,
                'CategoryCode'=>$request->CategoryCode,
                'Active'=>$request->status,
            ]);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Item  Updated Successfully'
            ]);
        } catch (\Exception $exception) {
            return response()->json([
                'status' => 'error',
                'message' => $exception->getMessage()
            ], 500);
        }

    }
}
