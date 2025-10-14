<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\DoctorModel;
use App\Models\RoleAssignModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;



class DoctorController extends Controller
{
    // add new users
    function addData(Request $request){
        
        $validator = Validator::make(request()->all(), [
          'f_name' => 'required',
          'l_name' => 'required',
          'email' => 'required',
          'password' => 'required',
          'department' => 'required',
          'ex_year' => 'required',
          'active' => 'required',
          'specialization' => 'required',
          'dob' => 'required',
          'gender' => 'required'
          
    ]);
        
    if ($validator->fails())
    
      return response (["response"=>400],400);
        else{
          
          if(isset($request->phone))
          {
            $alreadyAddedModel= User::where("phone",$request->phone)->first();
            if($alreadyAddedModel)
            {
                return Helpers::errorResponse("phone number already exists");
            
            }
          }
          if(isset($request->email))
          {
            $alreadyAddedModel= User::where("email",$request->email)->first();
            if($alreadyAddedModel)
            {
                return Helpers::errorResponse("email id already exists");
     
            }
          }
    
          if(isset($request->phone)){
            if(!is_numeric($request->phone)){
                return Helpers::errorResponse("Please enter valid phone number");

            }
          }
                  try{
                    DB::beginTransaction();
                    $timeStamp= date("Y-m-d H:i:s");
                    $userModel=new User;
                    if(isset($request->phone)){
                      $userModel->phone= $request->phone;
                    }
                    if(isset($request->email)){
                      $userModel->email= $request->email;
                    }
            
                    if(isset($request->password)){
                      $userModel->password= Hash::Make($request->password);
                    }
                    else if(!isset($request->password)){
                      $userModel->password= Hash::make(Str::random(8));
                    }
                    $userModel->f_name=$request->f_name;
                    $userModel->l_name=$request->l_name;

                    if(isset($request->dob)){
                        $userModel->dob= $request->dob;
                    
                      }
                      if(isset($request->gender)){
                        $userModel->gender= $request->gender;
                      }
                      if(isset($request->isd_code)){
                        $userModel->isd_code= $request->isd_code;
                      }
                

                                            
                    $userModel->created_at=$timeStamp;
                    $userModel->updated_at=$timeStamp;
                    if(isset($request->image)){
        
                        $userModel->image =  $request->hasFile('image') ? Helpers::uploadImage('users/', $request->file('image')) : null;
                  }
                    $qResponce= $userModel->save();

                
                    if($qResponce)
                   { 
                    $doctorModel=new DoctorModel;
                    $doctorModel->user_id =$userModel->id;
                  
                    $doctorModel->department =$request->department;

                    if(isset($request->description))
                    {
                        $doctorModel->description=$request->description;
                    }
                    $doctorModel->ex_year=$request->ex_year;
                    $doctorModel->specialization = $request->specialization;
                    $doctorModel->active=$request->active;

                    $doctorModel->created_at=$timeStamp;
                    $doctorModel->updated_at=$timeStamp;
                    $qResponceDoct= $doctorModel->save();
              
                    if($qResponceDoct)
                   { 
                      
                    $dataModel= new RoleAssignModel;
                    $dataModel->role_id=18;
                    $dataModel->user_id=$userModel->id;
                    $dataModel->created_at=$timeStamp;
                    $dataModel->updated_at=$timeStamp;
                        $dataModel->save();
                    DB::commit();
                    return Helpers::successWithIdResponse("successfully",$userModel->id);
                   }
             
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
    
      }
      function removeImage(Request $request){


        $validator = Validator::make(request()->all(), [
            'id' => 'required'
      ]);
      if ($validator->fails())
      return response (["response"=>400],400);
        try{
            $dataModel= User::where("id",$request->id)->first();
      
    
                $oldImage = $dataModel->image;
                if(isset($oldImage)){
                    if($oldImage!="def.png"){
                        Helpers::deleteImage($oldImage);
                    }
    
                    $dataModel->image=null;
                }
     
                $timeStamp= date("Y-m-d H:i:s");
                $dataModel->updated_at=$timeStamp;
                
                    $qResponce= $dataModel->save();
                    if($qResponce)
                    return Helpers::successResponse("successfully");
        
                    else 
                    return Helpers::errorResponse("error");
        }
        
    
     catch(\Exception $e){
              
                    return Helpers::errorResponse("error");
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

        //user data

        if(isset($request->phone))
        {   $alreadyAddedModel= User::where("phone",$request->phone)->where('id',"!=",$request->id)->first();
   
           if($alreadyAddedModel)
           {
            return Helpers::errorResponse("phone number already exists");
           }
        }
        if(isset($request->email))
        {   $alreadyAddedModel= User::where("email",$request->email)->where('id',"!=",$request->id)->first();
   
           if($alreadyAddedModel)
           {
            return Helpers::errorResponse("email id already exists");
           }
        }
        $userModel= User::where("id",$request->id)->first();
        if(isset($request->f_name)){
            $userModel->f_name = $request->f_name;
        }
        if(isset($request->l_name)){
            $userModel->l_name = $request->l_name;
        }

        if(isset($request->phone )){
            $userModel->phone = $request->phone ;
        }
        if(isset($request->isd_code)){
            $userModel->isd_code = $request->isd_code;
        }
        if(isset($request->gender )){
            $userModel->gender = $request->gender ;
        }
        if(isset($request->dob)){
            $userModel->dob = $request->dob;
        }
        if(isset($request->email )){
            $userModel->email = $request->email ;
        }
        if(isset($request->password)){
            $userModel->password = Hash::Make($request->password);
        }
       
        if(isset($request->isd_code_sec)){
            $userModel->isd_code_sec= $request->isd_code_sec;
        }
        if(isset($request->phone_sec)){
            $userModel->phone_sec= $request->phone_sec;
        }
        if(isset($request->address)){
            $userModel->address= $request->address;
        }
        if(isset($request->state)){
            $userModel->state= $request->state;
        }
        if(isset($request->city)){
            $userModel->city= $request->city;
        }
        if(isset($request->postal_code)){
            $userModel->postal_code= $request->postal_code;
        }

   
      
        if(isset($request->image)){
            if($request->hasFile('image') ){

            $oldImage = $userModel->image;
            $userModel->image =  Helpers::uploadImage('users/', $request->file('image'));
            if(isset($oldImage)){
                if($oldImage!="def.png"){
                    Helpers::deleteImage($oldImage);
                }
            }
        }
        }
        $res=$userModel->save();
        if($res){
              // doctors data 
            $dataModel= DoctorModel::where("user_id",$request->id)->first();

            if(isset($request->description)){
                $dataModel->description= $request->description;
            }
          
            if(isset($request->department )){
                $dataModel->department = $request->department;
            }
          
            if(isset($request->description)){
                $dataModel->description= $request->description;
            }
            if(isset($request->specialization)){
                $dataModel->specialization= $request->specialization;
            }
            if(isset($request->ex_year)){
                $dataModel->ex_year= $request->ex_year;
            }
            if(isset($request->zoom_client_id)){
                $dataModel->zoom_client_id= $request->zoom_client_id;
            }
            if(isset($request->zoom_secret_id)){
                $dataModel->zoom_secret_id= $request->zoom_secret_id;
            }
       
          
            if(isset($request->active)){
                $dataModel->active= $request->active;
            }
            if(isset($request->insta_link)){
                $dataModel->insta_link= $request->insta_link;
            }
            if(isset($request->fb_linik)){
                $dataModel->fb_linik= $request->fb_linik;
            }
            if(isset($request->twitter_link)){
                $dataModel->twitter_link= $request->twitter_link;
            }
            if(isset($request->you_tube_link)){
                $dataModel->you_tube_link= $request->you_tube_link;
            }
            if(isset($request->video_appointment)){
                $dataModel->video_appointment= $request->video_appointment;
            }
            if(isset($request->clinic_appointment)){
                $dataModel->clinic_appointment= $request->clinic_appointment;
            }
            if(isset($request->emergency_appointment)){
                $dataModel->emergency_appointment= $request->emergency_appointment;
            }

                // NEW: Add Out Call appointment fields
            if(isset($request->out_call_appointment)){
                $dataModel->out_call_appointment= $request->out_call_appointment;
            }
        
            if(isset($request->opd_fee)){
                $dataModel->opd_fee= $request->opd_fee;
            }
            if(isset($request->video_fee)){
                $dataModel->video_fee= $request->video_fee;
            }
            if(isset($request->emg_fee)){
                $dataModel->emg_fee= $request->emg_fee;
            }

            if(isset($request->stop_booking)){
                $dataModel->stop_booking= $request->stop_booking;
            }

            
            
        }else{
            DB::rollBack();
                return Helpers::errorResponse("error");
        }
        
        $timeStamp= date("Y-m-d H:i:s");
        $dataModel->updated_at=$timeStamp;
                $qResponce= $dataModel->save();
                if($qResponce)
                {
                    DB::commit();
                    return Helpers::successResponse("successfully");}
    
                else 
                {
                    DB::rollBack();
                    return Helpers::errorResponse("error");}
    }
    

 catch(\Exception $e){
    DB::rollBack();
                return Helpers::errorResponse("error");
              }
            }

            function getData(Request $request)
            {
                $searchQuery = $request->input('search_query');
            
                $data = DB::table("doctors")
                    ->select('doctors.*',
                        "users.f_name",
                        "users.l_name",
                        "users.phone",
                        "users.isd_code",
                        "users.gender",
                        "users.dob",
                        "users.email",
                        "users.image",
                        "users.city",
                        "department.title as department_name"
                    )
                    ->join('users', 'users.id', '=', 'doctors.user_id')
                    ->join('department', 'department.id', '=', 'doctors.department')
                    ->when($searchQuery, function ($query) use ($searchQuery) {
                        return $query->Where('users.f_name', 'LIKE', "%$searchQuery%")
                            ->orWhere('users.l_name', 'LIKE', "%$searchQuery%")
                            ->orWhere('doctors.specialization', 'LIKE', "%$searchQuery%")
                            ->orWhereRaw("CONCAT(f_name, ' ', l_name) LIKE ?", ["%$searchQuery%"]);
                    })
                    ->get();

                    foreach ($data as $dataDoctor) {
               
                        $dataDR = DB::table("doctors_review")
                        ->select('doctors_review.*')
                         ->where("doctors_review.doctor_id","=",$dataDoctor->user_id)
                          ->get();
                            // Calculate the total review points
                      $totalReviewPoints = $dataDR->sum('points'); // Assuming 'review_points' is the column name for review points
                  
                      // Count the number of reviews
                      $numberOfReviews = $dataDR->count();
                         // Calculate the average rating
                         $averageRating = $numberOfReviews > 0 ? number_format($totalReviewPoints / $numberOfReviews, 2) : '0.00';

                         $dataDoctor->total_review_points=$totalReviewPoints;
                         $dataDoctor->number_of_reviews=$numberOfReviews;
                         $dataDoctor->average_rating=$averageRating;


                         $dataDApp = DB::table("appointments")
                         ->select('appointments.*')
                          ->where("appointments.doct_id","=",$dataDoctor->user_id)
                           ->get();
                             // Calculate the total review points
                             $dataDoctor->total_appointment_done = count($dataDApp);
                    }
                    
             
            
                $response = [
                    "response" => 200,
                    'data' => $data,
                ];
            
                return response($response, 200);
            }


            
            function getDataActive(Request $request)
            {
                $searchQuery = $request->input('search_query');
            
                // $data = DB::table("doctors")
                //     ->select('doctors.*',
                //         "users.f_name",
                //         "users.l_name",
                //         "users.phone",
                //         "users.isd_code",
                //         "users.gender",
                //         "users.dob",
                //         "users.email",
                //         "users.image",
                //         "department.title as department_name"
                //     ) 
                //     ->where('doctors.active', 1)
                //     ->join('users', 'users.id', '=', 'doctors.user_id')
                //     ->join('department', 'department.id', '=', 'doctors.department')
                //     ->when($searchQuery, function ($query) use ($searchQuery) {
                //         return $query->Where('users.f_name', 'LIKE', "%$searchQuery%")
                //             ->orWhere('users.l_name', 'LIKE', "%$searchQuery%")
                //             ->orWhere('doctors.specialization', 'LIKE', "%$searchQuery%")
                //             ->orWhereRaw("CONCAT(f_name, ' ', l_name) LIKE ?", ["%$searchQuery%"]);
                //     })
                //     ->get();
                        $data = DB::table("doctors")
                    ->select('doctors.*',
                        "users.f_name",
                        "users.l_name",
                        "users.phone",
                        "users.isd_code",
                        "users.gender",
                        "users.dob",
                        "users.email",
                        "users.image",
                        "users.city",
                        "department.title as department_name"
                    )
                    ->join('users', 'users.id', '=', 'doctors.user_id')
                    ->join('department', 'department.id', '=', 'doctors.department')
                    ->where('doctors.active', 1)  // Ensure this is applied first
                    ->when($searchQuery, function ($query) use ($searchQuery) {
                        return $query->where(function ($subQuery) use ($searchQuery) {  // Group the search conditions
                            $subQuery->where('users.f_name', 'LIKE', "%$searchQuery%")
                                    ->orWhere('users.l_name', 'LIKE', "%$searchQuery%")
                                    ->orWhere('doctors.specialization', 'LIKE', "%$searchQuery%")
                                    ->orWhere('users.city', 'LIKE', "%$searchQuery%")       // Search by city
                                    ->orWhereRaw("CONCAT(users.f_name, ' ', users.l_name) LIKE ?", ["%$searchQuery%"]);
                        });
                    })
                    ->get();

                    foreach ($data as $dataDoctor) {
               
                        $dataDR = DB::table("doctors_review")
                        ->select('doctors_review.*')
                         ->where("doctors_review.doctor_id","=",$dataDoctor->user_id)
                          ->get();
                            // Calculate the total review points
                      $totalReviewPoints = $dataDR->sum('points'); // Assuming 'review_points' is the column name for review points
                  
                      // Count the number of reviews
                      $numberOfReviews = $dataDR->count();
                         // Calculate the average rating
                         $averageRating = $numberOfReviews > 0 ? number_format($totalReviewPoints / $numberOfReviews, 2) : '0.00';

                         $dataDoctor->total_review_points=$totalReviewPoints;
                         $dataDoctor->number_of_reviews=$numberOfReviews;
                         $dataDoctor->average_rating=$averageRating;


                         $dataDApp = DB::table("appointments")
                         ->select('appointments.*')
                          ->where("appointments.doct_id","=",$dataDoctor->user_id)
                           ->get();
                             // Calculate the total review points
                             $dataDoctor->total_appointment_done = count($dataDApp);
                    }
                    
             
            
                $response = [
                    "response" => 200,
                    'data' => $data,
                ];
            
                return response($response, 200);
            }
        
                   // get data by id
        
            function getDataById($id)
            {
        
                $data = DB::table("doctors")
                ->select('doctors.*',
                "users.f_name",
                "users.l_name",
                "users.phone",
                "users.isd_code",
                "users.gender",
                "users.dob",
                "users.email",
                "users.image",
                "users.address",
                "users.city",
                "users.state",
                "users.postal_code",
                "users.isd_code_sec",
                "users.phone_sec",
                "department.title as department_name"
                )
                ->Join('users','users.id','=','doctors.user_id')
                ->join('department', 'department.id', '=', 'doctors.department')
                ->where("doctors.user_id","=",$id)
                  ->first();
                
                    if($data!=null){
               
                            $dataDR = DB::table("doctors_review")
                            ->select('doctors_review.*')
                             ->where("doctors_review.doctor_id","=",$data->user_id)
                              ->get();
                                // Calculate the total review points
                          $totalReviewPoints = $dataDR->sum('points'); // Assuming 'review_points' is the column name for review points
                      
                          // Count the number of reviews
                          $numberOfReviews = $dataDR->count();
                             // Calculate the average rating
                             $averageRating = $numberOfReviews > 0 ? number_format($totalReviewPoints / $numberOfReviews, 2) : '0.00';
    
                             $data->total_review_points=$totalReviewPoints;
                             $data->number_of_reviews=$numberOfReviews;
                             $data->average_rating=$averageRating;

                             $dataDApp = DB::table("appointments")
                             ->select('appointments.*')
                              ->where("appointments.doct_id","=",$data->user_id)
                               ->get();
                                 // Calculate the total review points
                                 $data->total_appointment_done = count($dataDApp);
                        
                    }

                      $response = [
                          "response"=>200,
                          'data'=>$data,
                      ];
                  
                      
                return response($response, 200);
                }
                function getDataByDepId($id)
            {
        
                $data = DB::table("doctors")
                ->select('doctors.*',
                "users.f_name",
                "users.l_name",
                "users.phone",
                "users.isd_code",
                "users.gender",
                "users.dob",
                "users.email",
                "users.image",
                "department.title as department_name"
                )
                ->Join('users','users.id','=','doctors.user_id')
                ->join('department', 'department.id', '=', 'doctors.department')
                ->where("doctors.department","=",$id)
                  ->get();
                
                      $response = [
                          "response"=>200,
                          'data'=>$data,
                      ];
                  
                return response($response, 200);
                }

}

