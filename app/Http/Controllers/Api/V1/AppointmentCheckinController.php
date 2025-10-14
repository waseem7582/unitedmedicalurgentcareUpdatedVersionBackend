<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AppointmentCheckinModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\V1\NotificationCentralController;
class AppointmentCheckinController extends Controller
{

    function deleteData(Request $request){
      
        $initialCheck=false;
        $validator = Validator::make(request()->all(), [
          'id'=>'required'
      ]);
      if ($validator->fails())
      return response (["response"=>400],400);
      
        
        if ($initialCheck)
            return response (["response"=>400],400);
        
               try{
                  $timeStamp= date("Y-m-d H:i:s");
                  $dataModel= AppointmentCheckinModel::where("id",$request->id)->first();
              
                               
             $qResponce= $dataModel->delete();
             if($qResponce){
              $notificationCentralController = new NotificationCentralController();
              $notificationCentralController->sendAppointmentCheckInNotificationToUsers($dataModel->appointment_id,null,null,"Delete");
                return Helpers::successResponse("successfully");
            }
                       
          }catch(\Exception $e){
      
            return Helpers::errorResponse("error $e");
                   
              }
           
          
    }
     
    function getDataById($id)
    {      
    
            $data = DB::table("appointment_checkin")
            ->select('appointment_checkin.*')
              ->where('appointment_checkin.id','=',$id)
              ->first();
              $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
    function getData(Request $request)
    {      
    
           $data = DB::table("appointment_checkin")
            ->select('appointment_checkin.*')
              ->orderBy('appointment_checkin.date','DESC')
              ->orderBy('appointment_checkin.time','DESC');

              if (!empty($request->start_date)) {
                $data->whereDate('appointment_checkin.date', '>=', $request->start_date);
            }
            
            if (!empty($request->end_date)) {
                $data->whereDate('appointment_checkin.date', '<=', $request->end_date);
            }
            $data = $data->get();
              $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
    
    public function getDataPeg(Request $request)
    {
  
        // Calculate the limit
        $start = $request->start;
        $end = $request->end;
        $limit = ($end - $start);
  
        // Define the base query
  
  
        $query = DB::table("appointment_checkin")
        ->select('appointment_checkin.*',
        'appointments.doct_id',
        'users.f_name as doct_f_name',
        'users.l_name as doct_l_name',
        'patients.f_name as patient_f_name',
        'patients.l_name as patient_l_name'
        
        )
        ->join('appointments','appointments.id','=','appointment_checkin.appointment_id')
        ->Join('patients', 'patients.id', '=', 'appointments.patient_id')
        ->Join('users', 'users.id', '=', 'appointments.doct_id')
        ->orderBy('appointment_checkin.date','DESC')
        ->orderBy('appointment_checkin.time','DESC');
        if (!empty($request->doctor_id)) {
         
          $query->where('appointments.doct_id', '=', $request->doctor_id);
      }
        
            if (!empty($request->start_date)) {
              $query->whereDate('appointment_checkin.date', '>=', $request->start_date);
          }
          
          if (!empty($request->end_date)) {
              $query->whereDate('appointment_checkin.date', '<=', $request->end_date);
          }
    
     
  
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('appointment_checkin.appointment_id', 'like', '%' . $search . '%')
                ->orWhere(DB::raw('CONCAT(patients.f_name, " ", patients.l_name)'), 'like', '%' . $search . '%')
                ->orWhere(DB::raw('CONCAT(users.f_name, " ", users.l_name)'), 'like', '%' . $search . '%');
            });
        }
  
        $total_record = $query->get()->count();
        $data = $query->skip($start)->take($limit)->get();
  
        $response = [
            "response" => 200,
            "total_record" => $total_record,
            'data' => $data,
        ];
  
        return response()->json($response, 200);
    }
    function getDataByDoctIdAndDate($docto_id,$date)
    {      
      
           $data = DB::table("appointment_checkin")
            ->select('appointment_checkin.*',
            'patients.f_name as patient_f_name',
            'patients.l_name as patient_l_name',
            )  
            ->join('appointments','appointments.id','=','appointment_checkin.appointment_id')
            ->where('appointments.status',"Confirmed")
            ->where('appointments.doct_id',$docto_id)
            ->where('appointment_checkin.date',$date)
            ->join("patients","patients.id",'=','appointments.patient_id')
               ->orderBy('appointment_checkin.time','ASC')
              ->get();
              $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
    
    function addData(Request $request)
    {
        
        $validator = Validator::make(request()->all(), [
          'appointment_id' => 'required',
          'time' => 'required',
          'date' => 'required'
    ]);
        
    if ($validator->fails())
          return response (["response"=>400],400);
        
               $alreadyExists = AppointmentCheckinModel::where('appointment_id', '=', $request->appointment_id)
               ->where('date', '=', $request->date)
               ->first();
                if ($alreadyExists) {
                    return Helpers::errorResponse("Already checked in");
                }
                  try{
                    $timeStamp= date("Y-m-d H:i:s");
                    $dataModel=new AppointmentCheckinModel;
                    $dataModel->appointment_id = $request->appointment_id;
                      $dataModel->time = $request->time;
                      $dataModel->date = $request->date;
                    $dataModel->created_at=$timeStamp;
                    $dataModel->updated_at=$timeStamp;
                    $qResponce= $dataModel->save();
                    if($qResponce){

                      $notificationCentralController = new NotificationCentralController();
                      $notificationCentralController->sendAppointmentCheckInNotificationToUsers($request->appointment_id,$request->date,$request->time,"Add");
                        return Helpers::successWithIdResponse("successfully",$dataModel->id);
                    }   
                  }catch(\Exception $e){
              
                    return Helpers::errorResponse("error");
                  }
       
      }

      function updateDetails(Request $request){

        $validator = Validator::make(request()->all(), [
          'id'=>'required'
      ]);
      if ($validator->fails())
            return response (["response"=>400],400);
 
               try{
                  $timeStamp= date("Y-m-d H:i:s");
                  $dataModel= AppointmentCheckinModel::where("id",$request->id)->first();
            
            
                  if(isset($request->time ))
                  $dataModel->time = $request->time ;
                
                     $dataModel->updated_at=$timeStamp;
                
               
             $qResponce= $dataModel->save();
       
         
              if($qResponce){
                        return Helpers::successWithIdResponse("successfully",$dataModel->id);
                    }
                               
                  }catch(\Exception $e){
              
                    return Helpers::errorResponse("error");
                  }
        
        
          }
}