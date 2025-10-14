<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppointmentPaymentModel;
use App\Models\AllTransactionModel;

use Illuminate\Support\Facades\Validator;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;

class AppointmentPaymentController extends Controller
{
   

  function getDataByAppId($id)
  {
      $data = DB::table("appointment_payments")
      ->select('appointment_payments.*',
      'all_transaction.user_id',
      'all_transaction.patient_id',
      'all_transaction.appointment_id',
      'patients.f_name as patient_f_name',
      'patients.l_name as patient_l_name',
      'users.f_name as user_f_name',
      'users.l_name as user_l_name'
      )
      ->Join('all_transaction','all_transaction.id','=','appointment_payments.txn_id')
      ->LeftJoin('patients','patients.id','=','all_transaction.patient_id')
      ->LeftJoin('users','users.id','=','all_transaction.user_id')
      ->where('all_transaction.appointment_id',$id)
      ->OrderBy('appointment_payments.created_at','DESC')
      
        ->first();

            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
      }

    function getDataById($id)
    {
        $data = DB::table("appointment_payments")
        ->select('appointment_payments.*',
        'all_transaction.user_id',
        'all_transaction.patient_id',
        'all_transaction.appointment_id',
        'patients.f_name as patient_f_name',
        'patients.l_name as patient_l_name',
        'users.f_name as user_f_name',
        'users.l_name as user_l_name'
        )
        ->where("appointment_payments.id",$id)
        ->Join('all_transaction','all_transaction.id','=','appointment_payments.txn_id')
        ->LeftJoin('patients','patients.id','=','all_transaction.patient_id')
        ->LeftJoin('users','users.id','=','all_transaction.user_id')
        ->OrderBy('appointment_payments.created_at','DESC')
        
          ->first();

              $response = [
                  "response"=>200,
                  'data'=>$data,
              ];
          
        return response($response, 200);
        }

        function getData()
        {
          $data = DB::table("appointment_payments")
          ->select('appointment_payments.*',
          'all_transaction.user_id',
          'all_transaction.patient_id',
          'all_transaction.appointment_id',
          'patients.f_name as patient_f_name',
          'patients.l_name as patient_l_name',
          'users.f_name as user_f_name',
          'users.l_name as user_l_name'
          )
          ->Join('all_transaction','all_transaction.id','=','appointment_payments.txn_id')
          ->LeftJoin('patients','patients.id','=','all_transaction.patient_id')
          ->LeftJoin('users','users.id','=','all_transaction.user_id')
          ->OrderBy('appointment_payments.created_at','DESC')
            ->get();

                $response = [
                    "response"=>200,
                    'data'=>$data,
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
                $query = DB::table("appointment_payments")
                ->select('appointment_payments.*',
                'all_transaction.user_id',
                'all_transaction.patient_id',
                'all_transaction.appointment_id',
                'patients.f_name as patient_f_name',
                'patients.l_name as patient_l_name',
                'users.f_name as user_f_name',
                'users.l_name as user_l_name',
                 'appointments.doct_id'
                )
                ->Join('all_transaction','all_transaction.id','=','appointment_payments.txn_id')
                ->LeftJoin('patients','patients.id','=','all_transaction.patient_id')
                ->LeftJoin('users','users.id','=','all_transaction.user_id') 
                ->LeftJoin('appointments','appointments.id','=','all_transaction.appointment_id')
                ->orderBy('appointment_payments.created_at', 'DESC');

                if (!empty($request->doctor_id)) {
         
                    $query->where('appointments.doct_id', '=', $request->doctor_id);
                }

                if (!empty($request->start_date)) {
                    $query->whereDate('appointment_payments.created_at', '>=', $request->start_date);
                }
                
                if (!empty($request->end_date)) {
                    $query->whereDate('appointment_payments.created_at', '<=', $request->end_date);
                }
            
                if ($request->has('search')) {
                    $search = $request->input('search');
                    $query->where(function ($q) use ($search) {
                        $q->where(DB::raw('CONCAT(patients.f_name, " ", patients.l_name)'), 'like', '%' . $search . '%')
                        ->orWhere(DB::raw('CONCAT(users.f_name, " ", users.l_name)'), 'like', '%' . $search . '%')
                        ->orWhere('appointment_payments.id', 'like', '%' . $search . '%')  
                        ->orWhere('appointment_payments.txn_id', 'like', '%' . $search . '%')  
                        ->orWhere('appointment_payments.invoice_id', 'like', '%' . $search . '%')  
                        ->orWhere('appointment_payments.payment_method', 'like', '%' . $search . '%')  
                        ->orWhere('all_transaction.user_id', 'like', '%' . $search . '%')  
                        ->orWhere('all_transaction.patient_id', 'like', '%' . $search . '%')  
                        ->orWhere('all_transaction.appointment_id', 'like', '%' . $search . '%')  
                        ->orWhere('appointment_payments.amount', 'like', '%' . $search . '%');
                       
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