<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PatientFilesModel;
use Illuminate\Support\Facades\Validator;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\V1\NotificationCentralController;


class PatientFilesController extends Controller
{
    // add new users
    function addData(Request $request){
        
        $validator = Validator::make(request()->all(), [
          'file_name' => 'required',
          'file' => 'required',
          'patient_id' => 'required'
          
    ]);
        
    if ($validator->fails())
      return response (["response"=>400],400);
 
          try{
                    DB::beginTransaction();
                    $timeStamp= date("Y-m-d H:i:s");
                    $patientModel=new PatientFilesModel;
                  
                    $patientModel->file_name=$request->file_name;
                    $patientModel->patient_id=$request->patient_id;

                    $patientModel->created_at=$timeStamp;
                    $patientModel->updated_at=$timeStamp;
                    if(isset($request->file)){
        
                        $patientModel->file =  $request->hasFile('file') ? Helpers::uploadImage('patient_file/', $request->file('file')) : null;
                  }
                    $qResponce= $patientModel->save();

                
                    if($qResponce)
                   { 
                  
                    DB::commit();
                    $notificationCentralController = new NotificationCentralController();
                    $notificationCentralController->sendFileNotificationToUsers($patientModel->id,"Add");
                    return Helpers::successWithIdResponse("successfully",$patientModel->id);
             
                }
        
              else 
                 {  
                  DB::rollBack();
                  return Helpers::errorResponse("error");}
        
                               
                  }catch(\Exception $e){
                    DB::rollBack();
                    return Helpers::errorResponse("error $e");
                  }
                
       
    
      }
                function deleteData(Request $request){


                    $validator = Validator::make(request()->all(), [
                        'id' => 'required'
                  ]);
                  if ($validator->fails())
                  return response (["response"=>400],400);
                    try{
                        DB::beginTransaction();
                
                        $dataModel= PatientFilesModel::where("id",$request->id)->first();
                                  
                            $oldFile = $dataModel->file;
                            if(isset($oldFile)){
                                if($oldFile!="def.png"){
                                    Helpers::deleteImage($oldFile);
                                }
                            }
                                $qResponce= $dataModel->delete();
                                if($qResponce)
                                {
                                    DB::commit();
                                    // $notificationCentralController = new NotificationCentralController();
                                    // $notificationCentralController->sendFileNotificationToUsers($request->id,"Delete");
                                    return Helpers::successResponse("successfully");
                                }
                    
                                else 
                                {
                                    DB::rollBack();
                                    return Helpers::errorResponse("error");
                                }
                    }
                    
                
                 catch(\Exception $e){
                    DB::rollBack();
                                return Helpers::errorResponse("error$e");
                              }
                  }
    // Update Data

function updateData(Request $request){


    $validator = Validator::make(request()->all(), [
        'id' => 'required'
  ]);
  if ($validator->fails())
  return response (["response"=>400],400);
    try{
        DB::beginTransaction();

        $dataModel= PatientFilesModel::where("id",$request->id)->first();
        if(isset($request->file_name)){
            $dataModel->file_name = $request->file_name;
        }
          
        if(isset($request->file)){
            if($request->hasFile('file') ){

            $oldFile = $dataModel->file;
            $dataModel->file =  Helpers::uploadImage('patient_file/', $request->file('file'));
            if(isset($oldFile)){
                if($oldFile!="def.png"){
                    Helpers::deleteImage($oldFile);
                }
            }
        }
        }
        $timeStamp = date("Y-m-d H:i:s");
        $dataModel->updated_at=$timeStamp;
                $qResponce= $dataModel->save();
                if($qResponce)
                {
                    DB::commit();
                    $notificationCentralController = new NotificationCentralController();
                    $notificationCentralController->sendFileNotificationToUsers($request->id,"Update");
                    return Helpers::successResponse("successfully");}
    
                else 
                {
                    DB::rollBack();
                    return Helpers::errorResponse("error");}
    }
    

 catch(\Exception $e){
    DB::rollBack();
                return Helpers::errorResponse("error $e");
              }
            }

            function getData(Request $request)
            {
                $searchQuery = $request->query('search_query');
            
                $data = DB::table("patient_files")
                    ->select('patient_files.*',
                        "patients.f_name",
                        "patients.l_name",
                        "patients.phone",
                        "patients.isd_code"
                    )
                    ->join('patients', 'patients.id', '=', 'patient_files.patient_id')
                    ->when($searchQuery, function ($query) use ($searchQuery) {
                        return $query->Where('patients.f_name', 'LIKE', "%$searchQuery%")
                            ->orWhere('patients.l_name', 'LIKE', "%$searchQuery%")
                            ->orWhere('patients.phone', 'LIKE', "%$searchQuery%")
                            ->orWhereRaw("CONCAT(f_name, ' ', l_name) LIKE ?", ["%$searchQuery%"]);
                    })
                    ->orderBy('patient_files.created_at','DESC')
                    ->get();
                    
                  
                $response = [
                    "response" => 200,
                    'data' => $data,
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
          
          
                $query = DB::table("patient_files")
                ->select('patient_files.*',
                            "patients.f_name",
                            "patients.l_name",
                            "patients.phone",
                            "patients.isd_code"
                        )
                        ->join('patients', 'patients.id', '=', 'patient_files.patient_id')
                        ->orderBy('patient_files.created_at','DESC');
        
                
                    if (!empty($request->start_date)) {
                      $query->whereDate('patient_files.created_at', '>=', $request->start_date);
                  }
                  
                  if (!empty($request->end_date)) {
                      $query->whereDate('patient_files.created_at', '<=', $request->end_date);
                  }
            
          
                if ($request->has('search')) {
                    $search = $request->input('search');
                    $query->where(function ($q) use ($search) {
                        $q->where('patient_files.patient_id', 'like', '%' . $search . '%')
                        ->orWhere(DB::raw('CONCAT(patients.f_name, " ", patients.l_name)'), 'like', '%' . $search . '%')
                        ->orWhere('patients.phone', 'like', '%' . $search . '%')
                            ->orWhere('patient_files.file_name', 'like', '%' . $search . '%');
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
            
            function getDataByUid(Request $request)
            {
                $uid = $request->input('user_id');
                
                // Check if uid is provided
                if (!$uid) {
                    return response(["response" => 400, "message" => "User ID is required"], 400);
                }
                
                $searchQuery = $request->input('search_query');
            
                $data = DB::table("patient_files")
                    ->select('patient_files.*',
                        "patients.f_name",
                        "patients.l_name",
                        "patients.phone",
                        "patients.isd_code",
                        "patients.user_id as patients_user_id"
                    )
                    ->join('patients', 'patients.id', '=', 'patient_files.patient_id')
                    
                    // Apply user_id filter first
                    ->where('patients.user_id', '=', $uid)
                    
                    // Apply search conditions only if searchQuery exists
                    ->when($searchQuery, function ($query) use ($searchQuery) {
                        return $query->where(function ($q) use ($searchQuery) {
                            $q->where('patients.f_name', 'LIKE', "%$searchQuery%")
                              ->orWhere('patients.l_name', 'LIKE', "%$searchQuery%")
                              ->orWhere('patient_files.file_name', 'LIKE', "%$searchQuery%")
                              ->orWhere('patients.phone', 'LIKE', "%$searchQuery%")
                              ->orWhereRaw("CONCAT(f_name, ' ', l_name) LIKE ?", ["%$searchQuery%"]);
                        });
                    })
                    
                    ->orderBy('patient_files.created_at', 'DESC')
                    ->get();
                
                $response = [
                    "response" => 200,
                    'data' => $data ?? [],
                ];
            
                return response($response, 200);
            }
            function getDataByPatientIdSearch(Request $request)
            {
                $patient_id = $request->input('patient_id');
                
                // Check if uid is provided
                if (!$patient_id) {
                    return response(["response" => 400, "message" => "User ID is required"], 400);
                }
                
                $searchQuery = $request->input('search_query');
            
                $data = DB::table("patient_files")
                    ->select('patient_files.*',
                        "patients.f_name",
                        "patients.l_name",
                        "patients.phone",
                        "patients.isd_code",
                        "patients.user_id as patients_user_id"
                    )
                    ->join('patients', 'patients.id', '=', 'patient_files.patient_id')
                    
                    // Apply user_id filter first
                    ->where('patient_files.patient_id', '=', $patient_id)
                    
                    // Apply search conditions only if searchQuery exists
                    ->when($searchQuery, function ($query) use ($searchQuery) {
                        return $query->where(function ($q) use ($searchQuery) {
                            $q->where('patients.f_name', 'LIKE', "%$searchQuery%")
                              ->orWhere('patients.l_name', 'LIKE', "%$searchQuery%")
                              ->orWhere('patient_files.file_name', 'LIKE', "%$searchQuery%")
                              ->orWhere('patients.phone', 'LIKE', "%$searchQuery%")
                              ->orWhereRaw("CONCAT(f_name, ' ', l_name) LIKE ?", ["%$searchQuery%"]);
                        });
                    })
                    
                    ->orderBy('patient_files.created_at', 'DESC')
                    ->get();
                
                $response = [
                    "response" => 200,
                    'data' => $data ?? [],
                ];
            
                return response($response, 200);
            }

                   // get data by id
        
            function getDataById($id)
           {
            $data = DB::table("patient_files")
                ->select('patient_files.*',
                    "patients.f_name",
                    "patients.l_name",
                    "patients.phone",
                    "patients.isd_code"
                )
                ->join('patients', 'patients.id', '=', 'patient_files.patient_id')
                ->where('patient_files.id',$id)
                ->first();
                
              
            $response = [
                "response" => 200,
                'data' => $data,
            ];
        
            return response($response, 200);
           }


        //    getdata by patient id
            function getDataByPatientId($id)
           {
            $data = DB::table("patient_files")
                ->select('patient_files.*',
                    "patients.f_name",
                    "patients.l_name",
                    "patients.phone",
                    "patients.isd_code"
                )
                ->join('patients', 'patients.id', '=', 'patient_files.patient_id')
                ->where('patient_files.patient_id',$id)
                ->get();
                
              
            $response = [
                "response" => 200,
                'data' => $data,
            ];
        
            return response($response, 200);
           }

}

