<?php

namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\WebhookLogModel;
use App\Models\WebhookCentrelizeDataLogmodel;
use App\Http\Controllers\Api\V1\AllTransactionController;
use App\Http\Controllers\Api\V1\AppointmentController;
use Illuminate\Support\Facades\Hash;

class WebhookController extends Controller
{
  public function handleWebhookStripe(Request $request)
  {
      try{

        $dataConfig = DB::table("payment_gateway")
        ->select('payment_gateway.*')
        ->where('payment_gateway.title','=',"Stripe")
          ->first();
          if( $dataConfig==null){
           $this->updateWebhookLog("Invalid Webhook Secret Key",null,null,null);

           return response()->json(['error' => 'Invalid Webhook Secret Key'], 400);
          }
          
             
// Retrieve the Stripe secret for verifying the webhook signature
$endpointSecret = $dataConfig->webhook_secret_key;
//'whsec_iXNNVOAK4WDYBna79uPf11jdPiJSSD5r';

// Retrieve the payload and Stripe-Signature header
$payload = $request->getContent();
$sigHeader = $request->header('Stripe-Signature');

// Define the tolerance (default is 300 seconds)
$tolerance = 300;

// Extract the timestamp and the actual signature from the header
list($timestamp, $signature) = $this->extractSignature($sigHeader);

// Reconstruct the signed payload
$signedPayload = "{$timestamp}.{$payload}";

// Verify the signature by creating an HMAC with your webhook secret
$expectedSignature = hash_hmac('sha256', $signedPayload, $endpointSecret);

//  $paymentIntent = $event['data']['object'];
//  $payloadJson= $paymentIntent['metadata'];

   // Decode the payload to get the event data
   $event = json_decode($payload, true);
   $paymentIntent = $event['data']['object'];
   $payloadJsonConvert = json_encode($paymentIntent);

$timeStamp = date("Y-m-d H:i:s");
$this->updateWebhookLog($request,$paymentIntent['id']??"",$paymentIntent['status'],$payloadJsonConvert);

// Compare the expected signature with the one Stripe sent
if (hash_equals($expectedSignature, $signature) && (time() - $timestamp) < $tolerance) {
   // Signature is valid, process the event

   // Handle the event type (e.g., payment_intent.succeeded)
   switch ($event['type']) {
       case 'payment_intent.succeeded':

          $payLoadId=$paymentIntent['id'];
          $payload_notes = $paymentIntent['metadata']??"";
          $payloadNotesEncoded = json_encode($payload_notes);
       $this->centrelizeData($payloadNotesEncoded, $payLoadId);
           break;

       case 'payment_intent.payment_failed':
           $paymentIntent = $event['data']['object'];
           break;

       default:
           Log::info('Received unhandled event type: ' . $event['type']);
   }

   return response()->json(['status' => 'success'], 200);
} else {
   // Invalid signature
   // Log::error('Invalid Stripe webhook signature');

  $this->updateWebhookLog("Invalid Stripe webhook signature",null,null,null);
        
   return response()->json(['error' => 'Invalid signature'], 400);
}
      } catch(\Exception $e){
        $this->updateWebhookLog($e,null,null,null);
  return response()->json(['error' => 'Invalid signature'], 400);
      }
     
  }

  /**
   * Extract timestamp and signature from Stripe-Signature header.
   */
  private function extractSignature($sigHeader)
  {
      // The Stripe-Signature header contains a timestamp and the signature
      // Format: t=<timestamp>,v1=<signature>
      $sigParts = explode(',', $sigHeader);
      $timestamp = substr($sigParts[0], 2);
      $signature = substr($sigParts[1], 3);

      return [$timestamp, $signature];
  }

  public function updateWebhookLog($response,$payment_id,$status,$payload){
    $timeStamp = date("Y-m-d H:i:s");
    $webhookLogModel = new WebhookLogModel;
    $webhookLogModel->response=$response;
    $webhookLogModel->payment_id = $payment_id;
    $webhookLogModel->status = $status;
    $webhookLogModel->payload =  $payload;
    $webhookLogModel->created_at =$timeStamp;
    $webhookLogModel->updated_at =$timeStamp;
    $webhookLogModel->save();
  }
    public function handleWebhook(Request $request)
  
     {  
      $dataConfig = DB::table("payment_gateway")
      ->select('payment_gateway.*')
      ->where('payment_gateway.title','=',"Razorpay")
        ->first();
        if( $dataConfig==null){
         $this->updateWebhookLog("Invalid Webhook Secret Key",null,null,null);
         return response()->json(['error' => 'Invalid Webhook Secret Key'], 400);
        }
        

        $secret = $dataConfig->webhook_secret_key;
        // Razorpay signature verification
        $webhookBody = $request->getContent();
        $webhookSignature = $request->header('X-Razorpay-Signature');
                    
        if (!$this->verifySignature($webhookBody, $webhookSignature, $secret)) {
            
            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 400);
        }   
        try{
   
        $event = $request->input('event');
        $payload = $request->input('payload.payment.entity');
        $payloadJson = json_encode($payload);

        $this->updateWebhookLog($request,$payload['id']??"", $payload['status']??"",$payloadJson);
      
        if ($event === 'payment.captured') {
   
             $payLoadId=$payload['id'];
             $payload_notes = $payload['notes']??"";
             $payloadNotesEncoded = json_encode($payload_notes);
          $this->centrelizeData($payloadNotesEncoded, $payLoadId);

        }

        return response()->json(['status' => 'ok'],200);
     }
     catch(\Exception $e){
      $this->updateWebhookLog($e,null,null,null);
      return response()->json([
        'status' => 'faild',
    'msg'=>$e
    ],500);
    }
    }

    private function verifySignature($body, $signature, $secret)
    {
        $generatedSignature = hash_hmac('sha256', $body, $secret);
        return hash_equals($generatedSignature, $signature);
    }

    function centrelizeData($payloadJsonEncoded,$payLoadId)
   //   function centrelizeData(Request $request)
  {
    try{
     // $payloadJson = $request->input('payload');
     // $payload = $request->input('payload');
        $payloadJsonDecoded = json_decode($payloadJsonEncoded, true);
      $payload_id = $payLoadId;//$payload['id']??"";
     // $payload_notes = $payload['notes']??"";
      $payload_notes_payload= json_decode($payloadJsonDecoded['payload'],true);
    // dd($payload_notes_payload['user_id']);
   // Create an instance of AllTransactionController
   if($payloadJsonDecoded['type']=="Wallet"){
 
    // Simulate incoming request data by modifying the global request helper
    request()->merge([
      'user_id' => $payload_notes_payload['user_id'],
      'amount' =>  $payload_notes_payload['amount'],
      'payment_transaction_id' => $payload_id,
      'payment_method' => $payload_notes_payload['payment_method'],
      'transaction_type' =>  $payload_notes_payload['transaction_type'],
      'description' => $payload_notes_payload['description']
  ]);
  $transactionController = new AllTransactionController();
 $res=$transactionController->updateWalletMoneyData(request());
 $this-> updteCentrelizeDataLog($res,$payload_id,$payloadJsonEncoded);
   }
  
   if($payloadJsonDecoded['type']=="Appointment")
   { 
    
  request()->merge([
    'family_member_id' => $payload_notes_payload['family_member_id'] ?? null,
    'status' => !empty($payload_notes_payload['status']) ? $payload_notes_payload['status'] : null,
    'payment_transaction_id' => $payload_id ?? null,
    'date' => !empty($payload_notes_payload['date']) ? $payload_notes_payload['date'] : null,
    'time_slots' => !empty($payload_notes_payload['time_slots']) ? $payload_notes_payload['time_slots'] : null,
    'doct_id' => !empty($payload_notes_payload['doct_id']) ? $payload_notes_payload['doct_id'] : null,
    'dept_id' => !empty($payload_notes_payload['dept_id']) ? $payload_notes_payload['dept_id'] : null,
    'type' => !empty($payload_notes_payload['type']) ? $payload_notes_payload['type'] : null,
    'payment_status' => !empty($payload_notes_payload['payment_status']) ? $payload_notes_payload['payment_status'] : null,
    'fee' => !empty($payload_notes_payload['fee']) ? $payload_notes_payload['fee'] : null,
    'service_charge' => !empty($payload_notes_payload['service_charge']) ? $payload_notes_payload['service_charge'] : null,
    'total_amount' => !empty($payload_notes_payload['total_amount']) ? $payload_notes_payload['total_amount'] : null,
    'invoice_description' => !empty($payload_notes_payload['invoice_description']) ? $payload_notes_payload['invoice_description'] : null,
    'payment_method' => !empty($payload_notes_payload['payment_method']) ? $payload_notes_payload['payment_method'] : null,
    'user_id' => !empty($payload_notes_payload['user_id']) ? $payload_notes_payload['user_id'] : null,
    'is_wallet_txn' => !empty($payload_notes_payload['is_wallet_txn']) ? $payload_notes_payload['is_wallet_txn'] : null,
    'coupon_id' => !empty($payload_notes_payload['coupon_id']) ? $payload_notes_payload['coupon_id'] : null,
    'coupon_title' => !empty($payload_notes_payload['coupon_title']) ? $payload_notes_payload['coupon_title'] : null,
    'coupon_value' => !empty($payload_notes_payload['coupon_value']) ? $payload_notes_payload['coupon_value'] : null,
    'coupon_off_amount' => !empty($payload_notes_payload['coupon_off_amount']) ? $payload_notes_payload['coupon_off_amount'] : null,
    'unit_tax_amount' => !empty($payload_notes_payload['unit_tax_amount']) ? $payload_notes_payload['unit_tax_amount'] : null,
    'tax' => !empty($payload_notes_payload['tax']) ? $payload_notes_payload['tax'] : null,
    'unit_total_amount' => !empty($payload_notes_payload['unit_total_amount']) ? $payload_notes_payload['unit_total_amount'] : null,
    'source' =>  !empty($payload_notes_payload['source']) ? $payload_notes_payload['source'] : null,
]);

  $appointmentController = new AppointmentController();
  $res=$appointmentController->addData(request());
  $this-> updteCentrelizeDataLog($res,$payload_id,$payloadJsonEncoded);
   }

    }
    catch(\Exception $e){
      $this-> updteCentrelizeDataLog($e,$payload_id,$payloadJsonEncoded);

    }
     
  }
  
  public function updteCentrelizeDataLog($response,$payment_id,$payload){

    $timeStamp = date("Y-m-d H:i:s");
    $webhookCentrelizeDataLogmodel = new WebhookCentrelizeDataLogmodel;
    $webhookCentrelizeDataLogmodel->response=$response;
    $webhookCentrelizeDataLogmodel->payment_id = $payment_id;
    $webhookCentrelizeDataLogmodel->payload = $payload;
    $webhookCentrelizeDataLogmodel->created_at =$timeStamp;
    $webhookCentrelizeDataLogmodel->updated_at =$timeStamp;
    $webhookCentrelizeDataLogmodel->save();
  }

    }

