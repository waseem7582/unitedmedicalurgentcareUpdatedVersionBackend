<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RazorpayController extends Controller
{
    public function createOrder(Request $request)
    {
        // Check if the request method is POST
        if ($request->isMethod('post')) {
            // Get the request data
            $requestData = $request->all();

            // Check for required fields
            if (isset($requestData['apiKey'], $requestData['apiSecret'], $requestData['amount'])) {
                $apiKey = $requestData['apiKey'];
                $apiSecret = $requestData['apiSecret'];
                $amount = $requestData['amount'];
                $currency = $requestData['currency'];

                try {
                    // Make a POST request to Razorpay API to create an order
                    $response = Http::withBasicAuth($apiKey, $apiSecret)
                        ->withHeaders([
                            'Content-Type' => 'application/json',
                        ])
                        ->post('https://api.razorpay.com/v1/orders', [
                            'amount' => $amount,
                            'currency' => $currency,
                            'receipt' => 'Receipt no. 1',
                        ]);

                    if ($response->successful()) {
                        return response()->json($response->json(), 200);
                    }

                    return response()->json([
                        'error' => $response->body(),
                        'requestData' => $requestData
                    ], $response->status());
                } catch (\Exception $e) {
                    Log::error('Razorpay Order Creation Error: ' . $e->getMessage());
                    return response()->json([
                        'error' => 'An error occurred while creating the order.',
                        'requestData' => $requestData
                    ], 500);
                }
            } else {
                return response()->json([
                    'error' => 'Incomplete data provided',
                    'requestData' => $requestData
                ], 400);
            }
        } else {
            return response()->json([
                'error' => 'Method Not Allowed',
                'requestData' => $request->all()
            ], 405);
        }
    }
}
