<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppointmentModel;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use App\Models\User;
use App\Models\PatientModel;
use App\Models\ConfigurationsModel;
use DateTime;
use DateTimeZone;
class ZoomVideoCallController extends Controller
{
   
  
   public function createMeeting($appId,$date,$time)
    {
        $appointmentModel = AppointmentModel::where('id', $appId)->first();
           
            if($appointmentModel==null){

                return;
            }
            $doctId=$appointmentModel->doct_id;
            $patientId=$appointmentModel->patient_id;
            if(empty($doctId)){
                return;
            }
            if(empty($patientId)){
                return;
            }

        $doctFName="";
        $doctLName="";
        $patientFName="";
        $patientLName="";
        $patientPhone="";
        $userData = User::where('id', $doctId)->first();
        if($userData!=null){
            $doctFName= $userData->f_name??"";
            $doctLName=$userData->l_name??"";
        }

        $patintData = PatientModel::where('id', $patientId)->first();
        if($patintData!=null){
            $patientFName= $patintData->f_name??"";
            $patientLName= $patintData->l_name??"";
            $patientPhone=$patintData->phone??"";
        }
        $meetingDescription = $patientFName . ' ' . $patientLName . ' ' . $patientPhone . ' Meeting With ' . $doctFName . ' ' . $doctLName . ' Appointment ID: ' . $appId;

        $clientIdRes=ConfigurationsModel::where('id_name', 'zoom_client_id')->first();
        $clientSecretRes=ConfigurationsModel::where('id_name', 'zoom_client_secret')->first();
        $accountIdRes=ConfigurationsModel::where('id_name', 'zoom_account_id')->first();
                if($clientIdRes==null||$clientSecretRes==null||$accountIdRes==null){
                    return;
                }
        $clientId=$clientIdRes->value;
        $clientSecret=$clientSecretRes->value;
        $accountId=$accountIdRes->value;
             if (empty($clientId)) {
                 return;
                     }
                     if (empty($clientSecret)) {
                         return;
                             }
                             if (empty($accountId)) {
                                 return;
                                     }

                // Combine date and time and create a DateTime object
                $dateTime = new DateTime($date . ' ' . $time, new DateTimeZone('Asia/Kolkata'));

                // Convert to UTC
                $dateTime->setTimezone(new DateTimeZone('UTC'));

                // Format to ISO 8601
                $start_time = $dateTime->format('Y-m-d\TH:i:s\Z');

        $response = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post('https://zoom.us/oauth/token', [
                'grant_type' => 'account_credentials',
                'account_id'=> 'VL3JNfs_RSCdgmgtv1TZpA'
            ]);
            //echo $response;

        if ($response->successful()) {
             $accessToken = $response->json()['access_token'];
              // Meeting details from the request body
              $meetingDetails = [
                "topic" => $meetingDescription,
                "type" => 2,
                "start_time" => $start_time,  // Replace with the desired start time
                "duration" => 30,
                "password" => 123456,  // Replace with the desired password or generate dynamically
                "timezone" => "Asia/Kolkata",
                "agenda" => "Appointment Meeting",
                "settings" => [
                    "host_video" => false,
                    "participant_video" => true,
                    "cn_meeting" => false,
                    "in_meeting" => false,
                    "join_before_host" => true,
                    "mute_upon_entry" => true,
                    "watermark" => false,
                    "use_pmi" => false,
                    "approval_type" => 1,
                    "registration_type" => 1,
                    "audio" => "voip",
                    "auto_recording" => "none",
                    "waiting_room" => false
                ]
            ];
           // echo   $accessToken;

            // Create a Zoom meeting
            $meetingResponse = Http::withToken($accessToken)
            ->post('https://api.zoom.us/v2/users/me/meetings', $meetingDetails);
     
            if ($meetingResponse->successful()) {
                $responce=$meetingResponse->json();
                // echo $responce;
                $meetingId=$responce['id'];
                $joinUrl=$responce['join_url'];
              
                if($appointmentModel!=null){
                    $appointmentModel->meeting_id  =$meetingId;
                    $appointmentModel->meeting_link  =$joinUrl;
                    $appointmentModel->save();  
                }
         //      return response()->json($meetingResponse->json());
            }

            // return response()->json(['error' => 'Unable to create meeting', 'details' => $meetingResponse->json()], $meetingResponse->status());
        }

      //  return response()->json(['error' => 'Unable to retrieve token', 'details' => $response->json()], $response->status());
   }

   public function deleteMeeting($appId,$meetingId)
   {
   
    if($meetingId==null||$appId==null){return;}
       
       $clientIdRes=ConfigurationsModel::where('id_name', 'zoom_client_id')->first();
       $clientSecretRes=ConfigurationsModel::where('id_name', 'zoom_client_secret')->first();
       $accountIdRes=ConfigurationsModel::where('id_name', 'zoom_account_id')->first();
               if($clientIdRes==null||$clientSecretRes==null||$accountIdRes==null){
                   return;
               }
       $clientId=$clientIdRes->value;
       $clientSecret=$clientSecretRes->value;
       $accountId=$accountIdRes->value;
            if (empty($clientId)) {
                return;
                    }
                    if (empty($clientSecret)) {
                        return;
                            }
                            if (empty($accountId)) {
                                return;
                                    }
            
       $response = Http::withBasicAuth($clientId, $clientSecret)
           ->asForm()
           ->post('https://zoom.us/oauth/token', [
               'grant_type' => 'account_credentials',
               'account_id'=> $accountId
           ]);
          

       if ($response->successful()) {
            $accessToken = $response->json()['access_token'];
       
            $url="https://api.zoom.us/v2/meetings/".$meetingId;
           // Create a Zoom meeting

           $meetingResponse = Http::withToken($accessToken)
           ->delete($url);
    
           if ($meetingResponse->successful()) {
               $responce=$meetingResponse->json();
              
               $appointmentModel = AppointmentModel::where('id', $appId)->first();
               if($appointmentModel!=null){
                   $appointmentModel->meeting_id  =null;
                   $appointmentModel->meeting_link  =null;
                   $appointmentModel->save();  
               }
        //      return response()->json($meetingResponse->json());
           }

           // return response()->json(['error' => 'Unable to create meeting', 'details' => $meetingResponse->json()], $meetingResponse->status());
       }

     //  return response()->json(['error' => 'Unable to retrieve token', 'details' => $response->json()], $response->status());
  }
      public function updateMeeting($appId,$meetingId,$date,$time)
     { 
        if($meetingId==null||$appId==null){return;}
       
        $clientIdRes=ConfigurationsModel::where('id_name', 'zoom_client_id')->first();
        $clientSecretRes=ConfigurationsModel::where('id_name', 'zoom_client_secret')->first();
        $accountIdRes=ConfigurationsModel::where('id_name', 'zoom_account_id')->first();
                if($clientIdRes==null||$clientSecretRes==null||$accountIdRes==null){
                    return;
                }
        $clientId=$clientIdRes->value;
        $clientSecret=$clientSecretRes->value;
        $accountId=$accountIdRes->value;
             if (empty($clientId)) {
                 return;
                     }
                     if (empty($clientSecret)) {
                         return;
                             }
                             if (empty($accountId)) {
                                 return;
                                     }
             
        $response = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post('https://zoom.us/oauth/token', [
                'grant_type' => 'account_credentials',
                'account_id'=> $accountId
            ]);
           
 
        if ($response->successful()) {
             $accessToken = $response->json()['access_token'];
        
             $url="https://api.zoom.us/v2/meetings/".$meetingId;
            // Create a Zoom meeting
 
            $meetingResponse = Http::withToken($accessToken)
            ->delete($url);
     
            if ($meetingResponse->successful()) {
                $this->createMeeting ($appId,$date,$time);
            }
 
        }
  
     }
}
