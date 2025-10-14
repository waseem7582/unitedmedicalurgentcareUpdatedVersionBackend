<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\TestimonialsModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\ImageModel;
use Illuminate\Http\Request;
use App\CentralLogics\Helpers;
class TestimonialController extends Controller
{
    
    function deleteData(Request $request){
      
        $initialCheck=false;
        $validator = Validator::make(request()->all(), [
          'id'=>'required'
      ]);
      if ($validator->fails()){
        return Helpers::errorResponse("error");
      }
          DB::beginTransaction();

               try{
                  $timeStamp= date("Y-m-d H:i:s");
                  $dataModel= TestimonialsModel::where("id",$request->id)->first();
                  $oldImage=$dataModel->image;
            
                  if($oldImage!=null){
                   
                   Helpers::deleteImage($oldImage);
                  }
             $qResponce= $dataModel->delete();
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

    function updateData(Request $request){
        $initialCheck=false;
        $validator = Validator::make(request()->all(), [
          'id'=>'required'
      ]);
      if ($validator->fails())
      return response (["response"=>400],400);
      
               try{
                  $timeStamp= date("Y-m-d H:i:s");
                  $dataModel= TestimonialsModel::where("id",$request->id)->first();
            
                  if(isset($request->title ))
                  $dataModel->title = $request->title ;
                  if(isset($request->sub_title ))
                  $dataModel->sub_title = $request->sub_title ;
                  if(isset($request->rating ))
                  $dataModel->rating = $request->rating ;
                  if(isset($request->description ))
                  $dataModel->description = $request->description ;
                     $dataModel->updated_at=$timeStamp;
                     if(isset($request->image)){
                        if($request->hasFile('image') ){
                        $oldImage = $dataModel->image;
                        $dataModel->image =  Helpers::uploadImage('testimonial/', $request->file('image'));
                        if(isset($oldImage)){
                            if($oldImage!="def.png"){
                                Helpers::deleteImage($oldImage);
                            }
                        }
                    }
                }
               
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
    function getData()
    {

      $data = DB::table("testimonials")
      ->select('testimonials.*'
      )
            ->orderBy("testimonials.created_at","DESC")
        ->get();
      
            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
        }
    
    function getDataById($id)
    {

      $data = DB::table("testimonials")
      ->select('testimonials.*'
      )
       ->where("testimonials.id","=",$id)
        ->first();
            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
    }
  
    function addData(Request $request)
    {
        DB::beginTransaction();
        
        $validator = Validator::make(request()->all(), [
            'title' => 'required',
            'sub_title' => 'required',
            'rating' => 'required',
            'description' => 'required'
            
    ]);
  
    if ($validator->fails())
      return response (["response"=>400],400);
        else{
        
                  try{
                    $timeStamp= date("Y-m-d H:i:s");
                    $dataModel=new TestimonialsModel;
                    
                    $dataModel->title = $request->title ;
                    $dataModel->sub_title = $request->sub_title;
                    $dataModel->rating  = $request->rating;
                    $dataModel->description = $request->description;
      
                    if(isset($request->image)){
        
                        $dataModel->image =  $request->hasFile('image') ? Helpers::uploadImage('testimonial/', $request->file('image')) : null;
                  }
                 
                    $dataModel->created_at=$timeStamp;
                    $dataModel->updated_at=$timeStamp;
                    $qResponce= $dataModel->save();
                    if($qResponce)
                    {
                        DB::commit();
                        return Helpers::successResponse("successfully");
                      }
        
                    else 
                    {
                        DB::rollBack();
                   
                        return Helpers::errorResponse("error");
                      }
                               
                  }catch(\Exception $e){
                    DB::rollBack();
              
                    return Helpers::errorResponse("error");
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
            $dataModel= TestimonialsModel::where("id",$request->id)->first();
      
    
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
}
