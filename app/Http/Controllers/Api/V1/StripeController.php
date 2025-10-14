<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

class StripeController extends Controller
{
    public function createIntent(Request $request)
    {

        $validator = Validator::make(request()->all(), [
            'name' => 'required',
            'address_line1' => 'required',
            'city' => 'required',
            'state' => 'required' ,
            'country' => 'required' ,
            'STRIPE_SECRET_KEY' => 'required' ,
            'amount'=>'required',
            'currency'=>'required',
            'payload'=>'required',
            'type'=>'required',
            'description'=>'required'
          
    ]);

    if ($validator->fails())
      return response (["response"=>400],400);

        try {
            // Gather input data
            $name = $request->input('name');
            $addressLine1 = $request->input('address_line1');
            $city = $request->input('city');
            $state = $request->input('state');
            $country = $request->input('country');

            // Prepare body for POST request
            $body = [
                'name' => $name,
                'address[line1]' => $addressLine1,
                'address[city]' => $city,
                'address[state]' => $state,
                'address[country]' => $country,
            ];

            // Make POST request to Stripe
            $customerResponse = Http::asForm()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $request->STRIPE_SECRET_KEY,
                    'Content-Type' => 'application/x-www-form-urlencoded',
                ])
                ->post('https://api.stripe.com/v1/customers', $body);

                $customer = $customerResponse->json();
                if (!isset($customer['id'])) {
                    return Helpers::errorResponse("error");
                }

                    $amount = $request->input('amount');
                    $currency = $request->input('currency');
                    $amountInCents = $amount * 100; 
                    // Prepare body for payment intent creation
                    $paymentIntentBody = [
                        'amount' => $amountInCents, // amount in the smallest currency unit (e.g., cents)
                        'currency' => $currency,
                        'description' => $request->description,
                        'metadata' => [
                            'payload' => $request->payload,
                            'type'=> $request->type
                        ],
                        'customer' => $customer['id'], // attach the created customer
                        'shipping[address][line1]' => $addressLine1,
                        'shipping[name]' => $name,
                        'shipping[address][city]' => $city,
                        'shipping[address][state]' => $state,
                        'shipping[address][country]' => $country,
                    ];
        
                    // Make POST request to Stripe to create a payment intent
                    $paymentIntentResponse = Http::asForm()
                        ->withHeaders([
                            'Authorization' => 'Bearer ' .  $request->STRIPE_SECRET_KEY,
                            'Content-Type' => 'application/x-www-form-urlencoded',
                        ])
                        ->post('https://api.stripe.com/v1/payment_intents', $paymentIntentBody);
                        $paymentIntentResponseJson = $paymentIntentResponse->json();
                        
                    // return Helpers::successWithIdResponse("successfully", $paymentIntentResponseJson['id']);
                    // Return the payment intent response from Stripe
                    $response = [
                        "response"=>200,
                        'status'=>true,
                        'message' =>'successfully',
                        'id'=>$paymentIntentResponseJson['id'],
                        'client_secret'=>$paymentIntentResponseJson['client_secret'],
                        'customer_id'=>$customer['id']
              
                    ];
                  return response($response, 200);
                    return response()->json($paymentIntentResponse->json());
        } catch (\Exception $e) {
            // Handle exception and return error message
            return Helpers::errorResponse("error");
        }
    }
}
