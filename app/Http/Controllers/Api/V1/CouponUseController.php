<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CouponUseModel;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;

class CouponUseController extends Controller
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
                  $dataModel= CouponUseModel::where("id",$request->id)->first();
              
                               
             $qResponce= $dataModel->delete();
             if($qResponce){
                return Helpers::successResponse("successfully");
            }
                       
          }catch(\Exception $e){
      
            return Helpers::errorResponse("error");
                   
              }
          
    }
  
    function getDataById($id)
    {      
    
       
            $data = DB::table("coupon_use")
            ->select('coupon_use.*',
            'users.f_name',
            'users.l_name'
            )
           ->join('users','users.id','=','coupon_use.user_id')
              ->where('coupon_use.id','=',$id)
              ->first();
              $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
    function getData()
    {      
    
           $data = DB::table("coupon_use")
            ->select('coupon_use.*',
            'users.f_name',
            'users.l_name'
            )
            ->join('users','users.id','=','coupon_use.user_id')
              ->orderBy('coupon_use.created_at','DESC')
              ->get();
              $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }

}