<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $take = $request->take;
        $search = $request->search;
        $customers=  Customer::where(function ($q) use ($search) {
                $q->where('CustomerCode', 'like', '%' . $search . '%');
                $q->orWhere('CustomerName', 'like', '%' . $search . '%');
            })
            ->select('CustomerCode', 'CustomerName', 'Address','Active')
            ->paginate($take);
        return $customers;
    }
}
