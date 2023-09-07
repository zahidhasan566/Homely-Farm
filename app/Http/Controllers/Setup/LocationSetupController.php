<?php

namespace App\Http\Controllers\Setup;

use App\Http\Controllers\Controller;
use App\Models\ItemsCategory;
use App\Models\Location;
use App\Traits\CodeGeneration;
use Illuminate\Http\Request;

class LocationSetupController extends Controller
{
    use CodeGeneration;
    public function index(Request $request){
        $take = $request->take;
        $search = $request->search;
        $location=  Location::where(function ($q) use ($search) {
            $q->where('LocationCode', 'like', '%' . $search . '%');
            $q->orWhere('LocationName', 'like', '%' . $search . '%');
        })
            ->select('LocationCode', 'LocationName','Active')
            ->paginate($take);
        return $location;
    }
}
