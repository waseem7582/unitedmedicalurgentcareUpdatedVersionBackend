<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppointmentInvoiceModel;
use App\Models\AllTransactionModel;
use PDF;
use Illuminate\Support\Facades\Validator;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;

class AppointmentInvoiceController extends Controller
{
  public function generatePDF($id)
  {
      $invoice = DB::table("appointment_invoice")
      ->select('appointment_invoice.*',
      'patients.f_name as patient_f_name',
      'patients.l_name as patient_l_name',
      'patients.phone as patient_phone',
      'users.f_name as user_f_name',
      'users.l_name as user_l_name',
      'users.phone as user_phone',
      )
      ->LeftJoin('patients','patients.id','=','appointment_invoice.patient_id')
      ->LeftJoin('users','users.id','=','appointment_invoice.user_id')
          ->where("appointment_invoice.id", "=", $id)
          ->first();
  
      if (!$invoice) {
          return response()->json(['error' => 'Invoice not found'], 404);
      }
  
      $invoiceItems = DB::table("appointments_invoice_item")
          ->select('appointments_invoice_item.*')
          ->where("appointments_invoice_item.invoice_id", "=", $id)
          ->get();
      $invoice->items = $invoiceItems;

      $invoice->clinic_name = DB::table('configurations')
      ->where('id_name', '=', 'clinic_name')
      ->value('value');
      
      $invoice->logo = DB::table("configurations")
      ->where('id_name', '=', 'logo')
      ->value('value');

      $invoice->phone = DB::table("configurations")
      ->where('id_name', '=', 'phone')
      ->value('value');

      $invoice->phone_second = DB::table("configurations")
      ->where('id_name', '=', 'phone_second')
      ->value('value');
     
      $invoice->email = DB::table("configurations")
      ->where('id_name', '=', 'email')
      ->value('value');

      $invoice->address = DB::table("configurations")
      ->where('id_name', '=', 'address')
      ->value('value');
 
   
      

     $pdf = PDF::loadView('invoice.pdf', ['invoice' => $invoice]);
     return $pdf->stream('invoice.pdf', ['Attachment' => false]);
  }
  function getDataByAppId($id)
    {
      $data = DB::table("appointment_invoice")
      ->select('appointment_invoice.*',
      'patients.f_name as patient_f_name',
      'patients.l_name as patient_l_name',
      'users.f_name as user_f_name',
      'users.l_name as user_l_name'
      )
      ->LeftJoin('patients','patients.id','=','appointment_invoice.patient_id')
      ->LeftJoin('users','users.id','=','appointment_invoice.user_id')
      ->Where('appointment_invoice.appointment_id','=',$id)
      ->OrderBy('appointment_invoice.created_at','DESC')
        ->first();
        if($data!=null){
            $data->items= DB::table("appointments_invoice_item")
            ->select('appointments_invoice_item.*'
            )
            ->Where('appointments_invoice_item.invoice_id','=',$data->id)
            ->OrderBy('appointments_invoice_item.created_at','ASC')
              ->get();
        }

            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
        }

      
    function getDataById($id)
    {
      $data = DB::table("appointment_invoice")
      ->select('appointment_invoice.*',
      'patients.f_name as patient_f_name',
      'patients.l_name as patient_l_name',
      'users.f_name as user_f_name',
      'users.l_name as user_l_name'
      )
      ->LeftJoin('patients','patients.id','=','appointment_invoice.patient_id')
      ->LeftJoin('users','users.id','=','appointment_invoice.user_id')
      ->Where('appointment_invoice.id','=',$id)
      ->OrderBy('appointment_invoice.created_at','DESC')
        ->first();
        if($data!=null){
            $data->items= DB::table("appointments_invoice_item")
            ->select('appointments_invoice_item.*'
            )
            ->Where('appointments_invoice_item.invoice_id','=',$data->id)
            ->OrderBy('appointments_invoice_item.created_at','ASC')
              ->get();
        }

            $response = [
                "response"=>200,
                'data'=>$data,
            ];
        
      return response($response, 200);
        }

        function getData()
        {
          $data = DB::table("appointment_invoice")
          ->select('appointment_invoice.*',
          'patients.f_name as patient_f_name',
          'patients.l_name as patient_l_name',
          'users.f_name as user_f_name',
          'users.l_name as user_l_name'
          )
          ->LeftJoin('patients','patients.id','=','appointment_invoice.patient_id')
          ->LeftJoin('users','users.id','=','appointment_invoice.user_id')
          ->OrderBy('appointment_invoice.created_at','DESC')
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
                $query =  DB::table("appointment_invoice")
                ->select('appointment_invoice.*',
                'patients.f_name as patient_f_name',
                'patients.l_name as patient_l_name',
                'users.f_name as user_f_name',
                'users.l_name as user_l_name',
                'appointments.doct_id'
                )
                ->LeftJoin('patients','patients.id','=','appointment_invoice.patient_id')
                ->LeftJoin('users','users.id','=','appointment_invoice.user_id')
                ->LeftJoin('appointments','appointments.id','=','appointment_invoice.appointment_id')
             
                ->orderBy('appointment_invoice.created_at', 'DESC');

                if (!empty($request->doctor_id)) {
         
                    $query->where('appointments.doct_id', '=', $request->doctor_id);
                }

                if (!empty($request->start_date)) {
                    $query->whereDate('appointment_invoice.invoice_date', '>=', $request->start_date);
                }
                
                if (!empty($request->end_date)) {
                    $query->whereDate('appointment_invoice.invoice_date', '<=', $request->end_date);
                }
            
                if ($request->has('search')) {
                    $search = $request->input('search');
                    $query->where(function ($q) use ($search) {
                        $q->where(DB::raw('CONCAT(patients.f_name, " ", patients.l_name)'), 'like', '%' . $search . '%')
                        ->orWhere(DB::raw('CONCAT(users.f_name, " ", users.l_name)'), 'like', '%' . $search . '%')
                        ->orWhere('appointment_invoice.id', 'like', '%' . $search . '%')  
                        ->orWhere('appointment_invoice.user_id', 'like', '%' . $search . '%')  
                        ->orWhere('appointment_invoice.patient_id', 'like', '%' . $search . '%')  
                        ->orWhere('appointment_invoice.appointment_id', 'like', '%' . $search . '%')  
                        ->orWhere('appointment_invoice.status', 'like', '%' . $search . '%')  
                        ->orWhere('appointment_invoice.total_amount', 'like', '%' . $search . '%')  
                        ->orWhere('appointment_invoice.invoice_date', 'like', '%' . $search . '%');
                
                       
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
