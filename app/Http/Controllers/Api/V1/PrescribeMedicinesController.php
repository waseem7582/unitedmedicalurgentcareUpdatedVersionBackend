<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;

use App\Models\PrescribeMedicinesModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\CentralLogics\Helpers;
use Illuminate\Http\Request;

class PrescribeMedicinesController extends Controller
{
       //delete data
       function deleteData(Request $request){

    
        $validator = Validator::make(request()->all(), [
            'id' => 'required',
      ]);
      if ($validator->fails())
      return response (["response"=>400],400);
     
      try{               
                $dataModel= PrescribeMedicinesModel::where("id",$request->id)->first();
                                              
                $qResponce= $dataModel->delete();
                if($qResponce)
               {
             
                return Helpers::successWithIdResponse("successfully",$dataModel->id);}
            
                else 
                {  
                    
                    return Helpers::errorResponse("error");}
            
           
        }

     catch(\Exception $e){
             DB::rollBack();
              
                    return Helpers::errorResponse("error");
                  }
}
    function getDataById($id)
    {

      $data = DB::table("prescribe_medicines")
      ->select('prescribe_medicines.*')
       ->where("prescribe_medicines.id","=",$id)
        ->first();
      
            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
    function getData()
    {

      $data = DB::table("prescribe_medicines")
      ->select('prescribe_medicines.*')
       ->orderBy('prescribe_medicines.created_at','DESC')
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
            'title' => 'required'
            
    ]);
  
    if ($validator->fails())
      return response (["response"=>400],400);
        else{
        
                  try{
                    $prescribeMedicinesModel = PrescribeMedicinesModel::where("title", $request->title)->first();
                    if($prescribeMedicinesModel!=null){
                        return Helpers::errorResponse("Title is already exists");
                    }
                    $timeStamp= date("Y-m-d H:i:s");
                    $dataModel=new PrescribeMedicinesModel;
                    
                    $dataModel->title = $request->title ;
                 
                    if(isset($request->notes)){
                      $dataModel->notes  = $request->notes;
                    }
            
                  
                 
                    $dataModel->created_at=$timeStamp;
                    $dataModel->updated_at=$timeStamp;
                    $qResponce= $dataModel->save();
                    if($qResponce){
          
                        return Helpers::successWithIdResponse("successfully", $dataModel->id);
                            }else 
                    {
                      return Helpers::errorResponse("error");
                    }
                               
                  }catch(\Exception $e){
              
                    return Helpers::errorResponse("error");
                  }
                
            
      
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

        $dataModel= PrescribeMedicinesModel::where("id",$request->id)->first();
            $alreadyExists = PrescribeMedicinesModel::where('title', '=', $request->title)->where('id',"!=",$request->id)->first();
            if ($alreadyExists != null)
            {
                return Helpers::errorResponse("Title already exists");
            }
            else{
              $dataModel->notes  = $request->notes??null;
                  if(isset($request->title)){
                    $dataModel->title  = $request->title;
                  }
            }
        


        $timeStamp= date("Y-m-d H:i:s");
        $dataModel->updated_at=$timeStamp;
                $qResponce= $dataModel->save();
                if($qResponce)
                {
              
                    return Helpers::successResponse("successfully");}
    
                else 
                {
               
                    return Helpers::errorResponse("error");}
    }
    

 catch(\Exception $e){
                return Helpers::errorResponse("error");
              }
            }
}
