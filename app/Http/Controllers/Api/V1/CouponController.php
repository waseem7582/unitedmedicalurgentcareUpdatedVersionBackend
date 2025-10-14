<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CouponModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;

class CouponController extends Controller
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
                  $dataModel= CouponModel::where("id",$request->id)->first();
              
                               
             $qResponce= $dataModel->delete();
             if($qResponce){
                return Helpers::successResponse("successfully");
            }
                       
          }catch(\Exception $e){
      
            return Helpers::errorResponse("error");
                   
              }
           
          
    }
      function getDataByCoupon($title)
    {      
    
            $data = DB::table("coupon")
            ->select('coupon.*' )
              ->where('coupon.title','=',$title)
              ->first();
              $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
    function getDataById($id)
    {      
    
            $data = DB::table("coupon")
            ->select('coupon.*')
              ->where('coupon.id','=',$id)
              ->first();
              $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
    function getData()
    {      
    
           $data = DB::table("coupon")
            ->select('coupon.*')
              ->orderBy('coupon.start_date','DESC')
              ->get();
              $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
    function getDataActive()
    {      
    
           $data = DB::table("coupon")
            ->select('coupon.*')
            ->where('coupon.active','=',1)
              ->orderBy('coupon.start_date','DESC')
              ->get();
              $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }


    function getValidate(Request $request)
    {   
        $validator = Validator::make(request()->all(), [
            'title'=>'required',
             'user_id'=>'required'
        ]);
        if ($validator->fails())
              return response (["response"=>400],400);
 
              $currentDate= date("Y-m-d");
              

           $data = DB::table("coupon")
            ->select('coupon.*')
            ->where('coupon.title','=',$request->title)
            ->where('coupon.active','=',1)
            ->where('coupon.start_date', '<=', $currentDate)  // Check if start_date is <= current date
            ->where('coupon.end_date', '>=', $currentDate)  
              ->first();
      
              if($data==null){
                $msg="Invalid Coupon";
                $status=false;
                $response = [
                  "response"=>200,
                  "status"=>$status??true,
                  "msg"=>$msg??"Available",
                  'data'=>$data,
              ];
          
        return response($response, 200);
              }

              $dataCouponUse = DB::table("coupon_use")
              ->select('coupon_use.*')
              ->where('coupon_use.coupon_id','=',$data->id) 
              ->where('coupon_use.user_id','=',$request->user_id) 
                ->first();

                if($dataCouponUse!=null){
                  $msg="coupon code is already used";
                  $status=false;
                  $data=null;
                }

              $response = [
                "response"=>200,
                "status"=>$status??true,
                "msg"=>$msg??"Available",
                'data'=>$data,
            ];
        
      return response($response, 200);
    }


    function updateDetails(Request $request){

        $validator = Validator::make(request()->all(), [
          'id'=>'required'
      ]);
      if ($validator->fails())
            return response (["response"=>400],400);
 
               try{
                  $timeStamp= date("Y-m-d H:i:s");
                  $dataModel= CouponModel::where("id",$request->id)->first();
            
                
                    $alreadyExists = CouponModel::where('title', '=', $request->title)->where('id',"!=",$request->id)->first();
                  
                    if ($alreadyExists) {
                        return Helpers::errorResponse("title already exists");
                    }
                  if(isset($request->title ))
                  $dataModel->title = $request->title ;
                  
                    if(isset($request->value ))
                  $dataModel->value = $request->value ;
                  
                    if(isset($request->description ))
                  $dataModel->description = $request->description ;
                  
                    if(isset($request->active ))
                  $dataModel->active = $request->active ;
           
                  if(isset($request->start_date ))
                  $dataModel->start_date = $request->start_date ;
                  if(isset($request->end_date ))
                  $dataModel->end_date = $request->end_date ;

                     $dataModel->updated_at=$timeStamp;
                
               
             $qResponce= $dataModel->save();
       
         
              if($qResponce){
                        return Helpers::successWithIdResponse("successfully",$dataModel->id);
                    }
                               
                  }catch(\Exception $e){
              
                    return Helpers::errorResponse("error");
                  }
        
        
          }
    function addData(Request $request)
    {
        
        $validator = Validator::make(request()->all(), [
          'title' => 'required',
          'description' => 'required',
          'value' => 'required',
          'start_date' => 'required',
          'end_date' => 'required',
          'active' => 'required'

    ]);
        
    if ($validator->fails())
          return response (["response"=>400],400);
        
               $alreadyExists = CouponModel::where('title', '=', $request->title)->first();
                if ($alreadyExists) {
                    return Helpers::errorResponse("title already exists");
                }
                  try{
                    $timeStamp= date("Y-m-d H:i:s");
                    $dataModel=new CouponModel;
                    $dataModel->title = $request->title;
                      $dataModel->value = $request->value;
                      $dataModel->description = $request->description;
                         $dataModel->active = $request->active;
                         $dataModel->start_date = $request->start_date;
                         $dataModel->end_date = $request->end_date;
                    $dataModel->created_at=$timeStamp;
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