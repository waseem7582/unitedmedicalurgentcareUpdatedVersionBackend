<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\CentralLogics\Helpers;
class RazorpayPaymentController extends Controller
{  
    public function createOrder(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'amount' => 'required',
            'key' => 'required',   
            'secret' => 'required',
             'payload' => 'required',
             "type"=>"required"
    ]);
  
    if ($validator->fails())
      return response (["response"=>400],400);

      try{
        $amount = ( $request->amount)* 100;
        $key = $request->key;
        $secret = $request->secret;

        $response = Http::withBasicAuth($key, $secret)
                        ->post('https://api.razorpay.com/v1/orders', [
                            'amount' => $amount, // Convert amount to paise (Razorpay uses smallest currency unit)
                            'currency' => 'USD', // Change this if your currency is different
                            'receipt' => uniqid(), 
                            'notes' => [
                            'payload'=>$request->payload,
                            'type'=>$request->type
                           ]// Unique identifier for the order
                        ]);

        if ($response->successful()) {
           return Helpers::successWithIdResponse("successfully",  $response->json()['id']);
          // return response()->json(['success' => true, 'order' => $response->json()['id']]);
        } else {
            return Helpers::errorResponse("error");
            // return response()->json(['success' => false, 'message' => $response->json()['error']['description']]);
        }
    }catch(\Exception $e){
 
        return Helpers::errorResponse("error $e");
      }

    }


    public function capturePayment(Request $request)
    {
      
         $paymentId = $request->payment_id;
       $amount = $request->amount;
        $key = $request->key;
        $secret = $request->secret;

        $response = Http::withBasicAuth($key, $secret)
                        ->post("https://api.razorpay.com/v1/payments/{$paymentId}/capture", [
                            'amount' => $amount, // Convert amount to paise (Razorpay uses smallest currency unit)
                        ]);

        if ($response->successful()) {
            // Payment Captured Successfully
            return response()->json(['success' => true, 'message' => 'Payment captured successfully', 'payment' => $response->json()]);
        } else {
            // Handle Error
            return response()->json(['success' => false, 'message' => $response->json()['error']['description']]);
        }
    }
}
