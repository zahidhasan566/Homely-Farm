<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PaymentDetails;
use App\Models\SalesMaster;
use App\Traits\CodeGeneration;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerDueController extends Controller
{
    use CodeGeneration;
    public function index(Request $request)
    {
        $take = $request->take;
        $search = $request->search;
        $customerDue=  SalesMaster::where(function ($q) use ($search) {
            $q->where('SalesMaster.SalesCode', 'like', '%' . $search . '%');
            $q->orWhere('SalesMaster.CustomerCode', 'like', '%' . $search . '%');
            $q->orWhere('Customer.CustomerName', 'like', '%' . $search . '%');
        })
            ->join('Customer','Customer.CustomerCode','SalesMaster.CustomerCode')
            ->where('Paid', '!=', 'Y')
            ->select('SalesMaster.SalesCode','SalesMaster.SalesDate','SalesMaster.CustomerCode', 'Customer.CustomerName','SalesMaster.Paid', 'SalesMaster.Value','SalesMaster.PaidAmount');

        if ($request->type === 'export') {
            return response()->json([
                'data' => $customerDue->get(),
            ]);
        } else {
            return $customerDue->paginate($take);
        }
    }


    public function dueListUpdate(Request $request){
        $allCustomers = [];
        foreach ($request->finalPaymentRows as $item) {
            $allCustomers[$item['CustomerCode']][] = $item;
        }

        try{
            DB::beginTransaction();
            foreach ($allCustomers as $key => $singleCustomer) {
                $totalCurrentPayment = array_sum(array_column($singleCustomer, 'CurrentPayment'));
                $moneyRecNo =  $this->generatePaymentMoneyReceiveCode();
                Payment::create([
                    'MoneyRecNo'=>$moneyRecNo,
                    'PaymentDate'=> Carbon::now()->format('Y-m-d H:i:s'),
                    'CustomerCode'=>$singleCustomer[0]['CustomerCode'],
                    'PaymentAmount'=>$totalCurrentPayment,
                    'PaymentMode'=>'CASH',
                    'Reference'=>'',
                    'ChequeNo'=>'',
                    'ChequeDate'=>'',
                    'PreparedBy'=>Auth::user()->UserId,
                    'PreparedDate'=> Carbon::now()->format('Y-m-d H:i:s')
                ]);

                foreach ($singleCustomer as $singlePaymentDetails) {

                    //Payment Details Insert
                    $paymentDetails = new PaymentDetails();
                    $paymentDetails->MoneyRecNo = $moneyRecNo;
                    $paymentDetails->SalesCode = $singlePaymentDetails['SalesCode'];
                    $paymentDetails->PaymentAmount = $singlePaymentDetails['CurrentPayment'];
                    $paymentDetails->save();

                    //Update Sales Master
                    $existingSalesMaster  = SalesMaster::where('SalesCode',$singlePaymentDetails['SalesCode'])->first();

                    //dd($existingSalesMaster->PaidAmount);
                        if( floatval($existingSalesMaster->Value) <= floatval($existingSalesMaster->PaidAmount) + floatval($singlePaymentDetails['CurrentPayment'])){
                        $existingSalesMaster->Paid = 'Y';
                    }
                    $existingSalesMaster->PaidAmount = floatval($existingSalesMaster->PaidAmount) + floatval($singlePaymentDetails['CurrentPayment']);
                    $existingSalesMaster->save();
                }
            }
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Customer Due Updated Successfully'
            ], 200);
        }
        catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }


    }
}
