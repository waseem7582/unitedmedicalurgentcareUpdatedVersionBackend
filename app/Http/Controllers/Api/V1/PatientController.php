<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PatientModel;
use Illuminate\Support\Facades\Validator;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
class PatientController extends Controller
{
        //add new data
        function addData(Request $request){

            $validator = Validator::make(request()->all(), [
                'f_name' => 'required',
                'l_name' => 'required'
          ]);
          if ($validator->fails())
          return response (["response"=>400],400);
         
          try{
              
                    $timeStamp= date("Y-m-d H:i:s");
                    $dataModel=new PatientModel;
                    
                    $dataModel->f_name = $request->f_name ;
                    $dataModel->l_name = $request->l_name ;
        

                    if(isset($request->isd_code)){ $dataModel->isd_code = $request->isd_code ;}
                    if(isset($request->phone)){ $dataModel->phone = $request->phone;}
                    if(isset($request->city)){ $dataModel->city = $request->city ;}
                    if(isset($request->state)){ $dataModel->state = $request->state ;}
                    if(isset($request->address)){ $dataModel->address = $request->address ;}
                    if(isset($request->email)){ $dataModel->email = $request->email ;}
                    if(isset($request->gender)){ $dataModel->gender = $request->gender ;}
                    if(isset($request->dob)){ $dataModel->dob = $request->dob ;}
                    if(isset($request->postal_code)){ $dataModel->postal_code = $request->postal_code ;}
                    if(isset($request->notes)){ $dataModel->notes = $request->notes ;}
                    if(isset($request->user_id)){ $dataModel->user_id = $request->user_id ;}
                    
                    if(isset($request->image)){
        
                        $dataModel->image =  $request->hasFile('image') ? Helpers::uploadImage('patients/', $request->file('image')) : null;
                  }     
                  
                
                    $dataModel->created_at=$timeStamp;
                    $dataModel->updated_at=$timeStamp;
                   
                    $qResponce = $dataModel->save();
                    if($qResponce)
                   {
              
                    
                    return Helpers::successWithIdResponse("successfully",$dataModel->id);}
                
                    else 
                    { 
                        
                        return Helpers::errorResponse("error");}
                
            }
    
         catch(\Exception $e){
               
                
                        return Helpers::errorResponse("error");
                      }
    }

    // Update data
function updateData(Request $request){


    $validator = Validator::make(request()->all(), [
        'id' => 'required'
  ]);
  if ($validator->fails())
  return response (["response"=>400],400);
    try{
        $dataModel= PatientModel::where("id",$request->id)->first();

        if(isset($request->f_name)){ $dataModel->f_name = $request->f_name ;}
        if(isset($request->l_name)){ $dataModel->l_name = $request->l_name ;}
        if(isset($request->gender)){ $dataModel->gender = $request->gender ;}
        if(isset($request->isd_code)){ $dataModel->isd_code = $request->isd_code ;}
        if(isset($request->phone)){ $dataModel->phone = $request->phone;}
        if(isset($request->city)){ $dataModel->city = $request->city ;}
        if(isset($request->state)){ $dataModel->state = $request->state ;}
        if(isset($request->address)){ $dataModel->address = $request->address ;}
        if(isset($request->email)){ $dataModel->email = $request->email ;}
        if(isset($request->gender)){ $dataModel->gender = $request->gender ;}
        if(isset($request->dob)){ $dataModel->dob = $request->dob ;}
        if(isset($request->postal_code)){ $dataModel->postal_code = $request->postal_code ;}
        if(isset($request->notes)){ $dataModel->notes = $request->notes ;}
        if(isset($request->user_id)){ $dataModel->user_id = $request->user_id ;}

        if(isset($request->image)){
            if($request->hasFile('image') ){

            $oldImage = $dataModel->image;
            $dataModel->image =  Helpers::uploadImage('patients/', $request->file('image'));
            if(isset($oldImage)){
                if($oldImage!="def.png"){
                    Helpers::deleteImage($oldImage);
                }
            }
        }
        }
    
        $timeStamp= date("Y-m-d H:i:s");

        $dataModel->updated_at=$timeStamp;
                $qResponce= $dataModel->save();
                if($qResponce)
                {
              
                    return Helpers::successResponse("successfully");
                }
    
                else 
                {
               
                    return Helpers::errorResponse("error");
                }
    }
    

 catch(\Exception $e){
                return Helpers::errorResponse("error");
              }
            }


    // get data

    function getData()
    {

      $data = DB::table("patients")
      ->select('patients.*'
      )
        ->get();
      
            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
        }

           // get data by id

    function getDataById($id)
    {

      $data = DB::table("patients")
      ->select('patients.*')
      ->where('id','=',$id)
        ->first();
      
            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
        }

        function removeImage(Request $request){


            $validator = Validator::make(request()->all(), [
                'id' => 'required'
          ]);
          if ($validator->fails())
          return response (["response"=>400],400);
            try{
                $dataModel= PatientModel::where("id",$request->id)->first();
          
        
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

                    function getDataByUID($id)
                    {
                
                      $data = DB::table("patients")
                      ->select('patients.*'
                      )->where("user_id","=",$id)
                        ->get();
                      
                            $response = [
                                "response"=>200,
                                'data'=>$data,
                            ];
                        
                      return response($response, 200);
                        }

                        public function getDataByDate(Request $request)
                        {
                            // Retrieve the start and end dates from the request
                            $startDate = $request->input('start_date');
                            $endDate = $request->input('end_date');
                        
                        
                            $validator = Validator::make(request()->all(), [
                                   
                                'start_date' => 'required|date',
                                'end_date' => 'required|date|after_or_equal:start_date'
                                 
                          ]);
                        
                          if ($validator->fails())
                          return response (["response"=>400],400);

                          $startDate = Carbon::parse($startDate)->startOfDay()->toDateTimeString();
                          $endDate = Carbon::parse($endDate)->endOfDay()->toDateTimeString();
                        
                            // Query the appointments table with the date range
                            $data = DB::table("patients")
                                ->select(
                                    'patients.*',
                                )
                                ->whereBetween('patients.created_at', [$startDate, $endDate])
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
    $start =$request->start;
    $end =$request->end;
    $limit = ($end - $start);

    // Define the base query

    $query = DB::table("patients")
    ->select('patients.*'  )
    ->orderBy('patients.created_at', 'DESC');

    if (!empty($request->start_date)) {
        $query->whereDate('patients.created_at', '>=', $request->start_date);
    }
    
    if (!empty($request->end_date)) {
        $query->whereDate('patients.created_at', '<=', $request->end_date);
    }

    if ($request->has('search')) {
        $search = $request->input('search');
        $query->where(function ($q) use ($search) {
            $q->where(DB::raw('CONCAT(patients.f_name, " ", patients.l_name)'), 'like', '%' . $search . '%')
            ->orWhere('patients.id', 'like', '%' . $search . '%')  
            ->orWhere('patients.phone', 'like', '%' . $search . '%')  
            ->orWhere('patients.city', 'like', '%' . $search . '%')  
            ->orWhere('patients.state', 'like', '%' . $search . '%')  
            ->orWhere('patients.address', 'like', '%' . $search . '%')  
            ->orWhere('patients.email', 'like', '%' . $search . '%')  
            ->orWhere('patients.gender', 'like', '%' . $search . '%')  
            ->orWhere('patients.dob', 'like', '%' . $search . '%');
           
        });
    }
 
    $total_record= $query->get()->count();
    $data = $query->skip($start)->take($limit)->get();
    

    $response = [
        "response" => 200,
        "total_record"=>$total_record,
        'data' => $data,
    ];

    return response()->json($response, 200);
}
}
