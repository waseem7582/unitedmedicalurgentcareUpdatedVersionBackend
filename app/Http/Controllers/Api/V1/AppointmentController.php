<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AppointmentModel;
use App\Models\AppointmentInvoiceModel;
use App\Models\AppointmentPaymentModel;
use App\Models\AppointmentStatusLogModel;
use App\Models\AppointmentInvoiceItemModel;
use App\Models\AllTransactionModel;
use App\Models\User;
use App\Models\FamilyMembersModel;
use App\Models\PatientModel;
use Illuminate\Support\Facades\Validator;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\V1\ZoomVideoCallController;
use App\Models\CouponUseModel;
use App\Http\Controllers\Api\V1\NotificationCentralController;

class AppointmentController extends Controller
{
    //update status to paid
    function updateStatusToPaid(Request $request)
    {


        $validator = Validator::make(request()->all(), [
            'appointment_id' => 'required',
            "payment_method" => 'required'


        ]);
        if ($validator->fails())
            return response(["response" => 400], 400);

        try {
            DB::beginTransaction();
            $appointment_id = $request->appointment_id;
            $timeStamp = date("Y-m-d H:i:s");
            $date = date("Y-m-d");

            $dataInvoiceModel = AppointmentInvoiceModel::where('appointment_id', $appointment_id)->first();

            if ($dataInvoiceModel == null) {
                throw new \Exception('Error');
            }
            $dataTXNModel = new AllTransactionModel;
            $dataTXNModel->amount  = $dataInvoiceModel->total_amount;
            $dataTXNModel->user_id  = $dataInvoiceModel->user_id;
            $dataTXNModel->patient_id  = $dataInvoiceModel->patient_id;
            $dataTXNModel->transaction_type = "Debited";
            $dataTXNModel->created_at = $timeStamp;
            $dataTXNModel->updated_at = $timeStamp;

            $qResponceTxn = $dataTXNModel->save();
            if (!$qResponceTxn) {
                DB::rollBack();
                return Helpers::errorResponse("error");
            }

            $dataPaymentModel = new AppointmentPaymentModel;
            $dataPaymentModel->txn_id = $dataTXNModel->id;
            $dataPaymentModel->invoice_id   = $dataInvoiceModel->id;
            $dataPaymentModel->amount   = $dataInvoiceModel->total_amount;
            $dataPaymentModel->payment_time_stamp   = $timeStamp;
            $dataPaymentModel->payment_method   = $request->payment_method;
            $dataPaymentModel->created_at = $timeStamp;
            $dataPaymentModel->updated_at = $timeStamp;
            $qResponcePayment = $dataPaymentModel->save();

            $dataTXNModel->appointment_id = $appointment_id;
            $dataTXNModel->save();

            $dataInvoiceModel->status = "Paid";
            $resDataInvoiceModel = $dataInvoiceModel->save();
            if (!$resDataInvoiceModel) {
                DB::rollBack();
                return Helpers::errorResponse("error");
            }

            $appModel = AppointmentModel::where("id", $appointment_id)->first();
            $appModel->payment_status = "Paid";
            $resAppModel = $appModel->save();
            if (!$resAppModel) {
                DB::rollBack();
                return Helpers::errorResponse("error");
            }

            DB::commit();
            return Helpers::successWithIdResponse("successfully", $appointment_id);
        } catch (\Exception $e) {
            DB::rollBack();

            return Helpers::errorResponse("error");
        }
    }

    //add new data
    function addData(Request $request)
    {

        $validator = Validator::make(request()->all(), [

            'status' => 'required',
            'date' => 'required',
            'time_slots' => 'required',
            'doct_id' => 'required',
            'dept_id' => 'required',
            'type' => 'required',
            'payment_status' => 'required',
            'total_amount' => 'required',
            'fee' => 'required',
            'service_charge' => 'required',
            'invoice_description' => 'required'

        ]);

        if ($validator->fails())
            return response(["response" => 400], 400);

        // In AppointmentController.php - update the validation
        if ($request->type == "Out Call") {
            $outCallValidator = Validator::make(request()->all(), [
                'out_call_address' => 'required',
                'out_call_city' => 'required',
            ]);
            
            if ($outCallValidator->fails()) {
                return response(["response" => 400, "message" => "Out Call address and city are required"], 400);
            }
        }

        try {
            DB::beginTransaction();
            $timeStamp = date("Y-m-d H:i:s");
            $date = date("Y-m-d");
            if (isset($request->family_member_id) && isset($request->patient_id)) {
                DB::rollBack();
                return Helpers::errorResponse("error");
            }

            $patientId = $request->patient_id;

            if (!isset($request->patient_id)) {
                if (!isset($request->family_member_id)) {

                    DB::rollBack();
                    return Helpers::errorResponse("error");
                }
                $dataFamilyModel = FamilyMembersModel::where('id', $request->family_member_id)->first();
                if ($dataFamilyModel == null) {
                    DB::rollBack();
                    return Helpers::errorResponse("error");
                }
                $dataPatientModelExists = PatientModel::where('f_name', $dataFamilyModel->f_name)
                    ->where('l_name', $dataFamilyModel->l_name)
                    ->where('phone', $dataFamilyModel->phone)
                    ->first();
                if ($dataPatientModelExists == null) {
                    $dataPatientModel = new PatientModel;
                    $dataPatientModel->f_name = $dataFamilyModel->f_name;
                    $dataPatientModel->l_name = $dataFamilyModel->l_name;
                    $dataPatientModel->phone = $dataFamilyModel->phone;
                    $dataPatientModel->user_id = $dataFamilyModel->user_id;
                    $dataPatientModel->isd_code = $dataFamilyModel->isd_code;
                    $dataPatientModel->dob = $dataFamilyModel->dob;
                    $dataPatientModel->gender = $dataFamilyModel->gender;
                    $resPatient = $dataPatientModel->save();
                    if (!$resPatient) {
                        DB::rollBack();
                        return Helpers::errorResponse("error");
                    }
                    $patientId = $dataPatientModel->id;
                } else {
                    $patientId = $dataPatientModelExists->id;
                }
            }

            if (isset($request->payment_transaction_id)) {


                $dataTXNModel = new AllTransactionModel;
                $dataTXNModel->payment_transaction_id = $request->payment_transaction_id;
                $dataTXNModel->amount  = $request->total_amount;
                $dataTXNModel->user_id  = $request->user_id;
                $dataTXNModel->patient_id  = $patientId;

                $dataTXNModel->transaction_type = "Debited";
                $dataTXNModel->created_at = $timeStamp;
                $dataTXNModel->updated_at = $timeStamp;
                $dataTXNModel->is_wallet_txn = $request->is_wallet_txn ?? 0;
                $qResponceTxn = $dataTXNModel->save();
                if (!$qResponceTxn) {
                    DB::rollBack();
                    return Helpers::errorResponse("error");
                }
                if ($request->is_wallet_txn) {

                    $userModel = User::where("id", $request->user_id)->first();
                    if (!$userModel) {
                        DB::rollBack();
                        return Helpers::errorResponse("error");
                    }
                    $userOldAmount = $userModel->wallet_amount ?? 0;
                    $deductAmount = $request->total_amount;
                    if ($userOldAmount < $deductAmount) {
                        DB::rollBack();
                        return Helpers::errorResponse("Iinsufficient amount in wallet");
                    }

                    $userNewAmount = $userOldAmount - $deductAmount;
                    $userModel->wallet_amount = $userNewAmount;
                    $qResponceWU = $userModel->save();
                    if (!$qResponceWU) {
                        DB::rollBack();
                        return Helpers::errorResponse("error");
                    }

                    $dataTXNModel->last_wallet_amount = $userOldAmount;
                    $dataTXNModel->new_wallet_amount = $userNewAmount;
                    $qResponceTxnWalletUpdate = $dataTXNModel->save();
                    if (!$qResponceTxnWalletUpdate) {
                        DB::rollBack();
                        return Helpers::errorResponse("error");
                    }
                }
            }

            $dataModel = new AppointmentModel;


            $dataModel->patient_id = $patientId;
            $dataModel->status = $request->status;
            $dataModel->date = $request->date;
            $dataModel->time_slots = $request->time_slots;
            $dataModel->doct_id = $request->doct_id;
            $dataModel->dept_id = $request->dept_id;
            $dataModel->type = $request->type;
            $dataModel->source = $request->source;
            $dataModel->payment_status = $request->payment_status;
            if (isset($request->meeting_id)) {
                $dataModel->meeting_id = $request->meeting_id;
            }
            if (isset($request->meeting_link)) {
                $dataModel->meeting_link = $request->meeting_link;
            }

            // ADD OUT CALL FIELDS - NEW CODE
            if ($request->type == "Out Call") {
                $dataModel->out_call_address = $request->out_call_address;
                $dataModel->out_call_city = $request->out_call_city;
                $dataModel->out_call_landmark = $request->out_call_landmark;
                $dataModel->out_call_instructions = $request->out_call_instructions;
            }

            $dataModel->created_at = $timeStamp;
            $dataModel->updated_at = $timeStamp;

            $qResponce = $dataModel->save();

            if ($qResponce) {
                if (isset($request->coupon_id)) {
                    $dataCouponUseModel = new CouponUseModel;
                    $dataCouponUseModel->user_id = $request->user_id;
                    $dataCouponUseModel->appointment_id  =  $dataModel->id;
                    $dataCouponUseModel->coupon_id   = $request->coupon_id;
                    $dataCouponUseModel->created_at = $timeStamp;
                    $dataCouponUseModel->updated_at = $timeStamp;
                    $dataCouponUseModel->save();
                    if (!$dataCouponUseModel) {
                        throw new \Exception('Error');
                    }
                }


                $dataInvoiceModel = new AppointmentInvoiceModel;
                $dataInvoiceModel->patient_id = $patientId;
                $dataInvoiceModel->user_id = $request->user_id;

                $dataInvoiceModel->appointment_id  = $dataModel->id;
                $dataInvoiceModel->status = $request->payment_status;
                $dataInvoiceModel->total_amount  = $request->total_amount;
                $dataInvoiceModel->invoice_date = $date;
                $dataInvoiceModel->created_at = $timeStamp;
                $dataInvoiceModel->updated_at = $timeStamp;
                $dataInvoiceModel->coupon_title = $request->coupon_title;
                $dataInvoiceModel->coupon_value = $request->coupon_value;
                $dataInvoiceModel->coupon_off_amount = $request->coupon_off_amount;
                $dataInvoiceModel->coupon_id = $request->coupon_id;
                $qResponceInvoice = $dataInvoiceModel->save();

                if ($qResponceInvoice) {

                    $dataInvoiceItemModel = new AppointmentInvoiceItemModel;
                    $dataInvoiceItemModel->invoice_id = $dataInvoiceModel->id;
                    $dataInvoiceItemModel->description  = $request->invoice_description;
                    $dataInvoiceItemModel->quantity = 1;
                    $dataInvoiceItemModel->unit_price  = $request->fee;
                    $dataInvoiceItemModel->service_charge =  $request->service_charge;
                    $dataInvoiceItemModel->total_price = $request->unit_total_amount;
                    $dataInvoiceItemModel->unit_tax  = $request->tax ?? 0;
                    $dataInvoiceItemModel->unit_tax_amount  = $request->unit_tax_amount ?? 0;

                    $dataInvoiceItemModel->created_at = $timeStamp;
                    $dataInvoiceItemModel->updated_at = $timeStamp;

                    $qResponceInvoiceItem = $dataInvoiceItemModel->save();
                    if ($qResponceInvoiceItem) {
                        if (isset($request->payment_transaction_id)) {
                            $dataPaymentModel = new AppointmentPaymentModel;
                            $dataPaymentModel->txn_id = $dataTXNModel->id;
                            $dataPaymentModel->invoice_id   = $dataInvoiceModel->id;
                            $dataPaymentModel->amount   = $request->total_amount;
                            $dataPaymentModel->payment_time_stamp   = $timeStamp;
                            $dataPaymentModel->payment_method   = $request->payment_method;
                            $dataPaymentModel->created_at = $timeStamp;
                            $dataPaymentModel->updated_at = $timeStamp;
                            $qResponcePayment = $dataPaymentModel->save();
                        }
                        if (isset($request->payment_transaction_id)) {
                            $dataTXNModel->appointment_id = $dataModel->id;
                            $dataTXNModel->save();
                        }


                        DB::commit();
                        if ($request->type == "Video Consultant") {
                            // Pass dependencies if needed
                            $zoomController = new ZoomVideoCallController();
                            $zoomController->createMeeting($dataModel->id, $dataModel->date, $dataModel->time_slots); // Ensure it doesn't rely on constructor dependencies
                        }

                        $notificationCentralController = new NotificationCentralController();
                        $notificationCentralController->sendAppointmentNotificationToUsers($dataModel->id);

                        return Helpers::successWithIdResponse("successfully", $dataModel->id);
                    } else {
                        throw new \Exception('Error');
                    }
                } else {
                    throw new \Exception('Error');
                }
            } else {
                throw new \Exception('Error');
            }
        } catch (\Exception $e) {
            DB::rollBack();

            return Helpers::errorResponse("error $e");
        }
    }
    // appointment resch
    function appointmentResch(Request $request)
    {

        $validator = Validator::make(request()->all(), [
            'id' => 'required',
            'time_slots' => 'required',
            'date' => 'required',
        ]);
        if ($validator->fails())
            return response(["response" => 400], 400);
        try {
            DB::beginTransaction();


            $dataModel = AppointmentModel::where("id", $request->id)->first();
            if ($dataModel == null) {
                DB::rollBack();
                return Helpers::errorResponse("error");
            }
            $oldTime = $dataModel->time_slots;
            $oldDate = $dataModel->date;
            $currentStatus = $dataModel->status;
            if ($currentStatus == "Rejected" || $currentStatus == "Cancelled") {
                DB::rollBack();
                return Helpers::errorResponse("Cannot update status");
            }
            $dataModel->status = 'Rescheduled';
            $dataModel->time_slots = $request->time_slots;
            $dataModel->date = $request->date;
            $timeStamp = date("Y-m-d H:i:s");
            $dataModel->updated_at = $timeStamp;
            $qResponce = $dataModel->save();
            if (!$qResponce) {
                DB::rollBack();

                return Helpers::errorResponse("error");
            }

            $appointmentData = DB::table("appointments")
                ->select('appointments.*', 'patients.user_id')
                ->join("patients", "patients.id", '=', 'appointments.patient_id')
                ->where("appointments.id", "=", $request->id)
                ->first();
            $userId = $appointmentData->user_id;
            $patient_id = $appointmentData->patient_id;
            if ($patient_id == null) {
                DB::rollBack();
                return Helpers::errorResponse("error");
            }
            $dataASLModel = new AppointmentStatusLogModel;
            $dataASLModel->appointment_id  =  $request->id;
            $dataASLModel->user_id  = $userId;
            $dataASLModel->status  = "Rescheduled";
            $dataASLModel->patient_id  = $patient_id;
            $dataASLModel->notes  = "Appointment " . $oldDate . " " . $oldTime . " rescheduled to " . $request->date . " " . $request->time_slots;
            $dataASLModel->created_at = $timeStamp;
            $dataASLModel->updated_at = $timeStamp;
            $qResponceApp = $dataASLModel->save();
            if (!$qResponceApp) {
                DB::rollBack();
                return Helpers::errorResponse("error");
            }
            DB::commit();
            if ($dataModel->type == "Video Consultant") {
                // Pass dependencies if needed
                $zoomController = new ZoomVideoCallController();
                $zoomController->updateMeeting($dataModel->id, $dataModel->meeting_id, $request->date, $request->time_slots); // Ensure it doesn't rely on constructor dependencies
            }
            $notificationCentralController = new NotificationCentralController();
            $notificationCentralController->sendWalletRshNotificationToUsersAgainstRejected($request->id, $oldDate, $oldTime);

            return Helpers::successResponse("successfully");
        } catch (\Exception $e) {
            DB::rollBack();
            return Helpers::errorResponse("error $e");
        }
    }

    // Update data
    function updateStatus(Request $request)
    {


        $validator = Validator::make(request()->all(), [
            'id' => 'required',
            "status" => 'required'
        ]);
        if ($validator->fails())
            return response(["response" => 400], 400);
        try {
            DB::beginTransaction();
            if ($request->status == "Cancelled") {
                DB::rollBack();
                return Helpers::errorResponse("Cannot update status");
            }

            $dataModel = AppointmentModel::where("id", $request->id)->first();
            $currentStatus = $dataModel->status;
            if ($currentStatus == "Rejected" || $currentStatus == "Cancelled") {
                DB::rollBack();
                return Helpers::errorResponse("Cannot update status");
            }
            $dataModel->status = $request->status;
            $timeStamp = date("Y-m-d H:i:s");
            $dataModel->updated_at = $timeStamp;
            $qResponce = $dataModel->save();
            if ($qResponce) {

                $appointmentData = DB::table("appointments")
                    ->select('appointments.*', 'patients.user_id')
                    ->join("patients", "patients.id", '=', 'appointments.patient_id')
                    ->where("appointments.id", "=", $request->id)
                    ->first();
                $userId = $appointmentData->user_id;
                $patient_id = $appointmentData->patient_id;
                if ($patient_id == null) {
                    DB::rollBack();
                    return Helpers::errorResponse("error");
                }
                $dataASLModel = new AppointmentStatusLogModel;
                $dataASLModel->appointment_id  =  $request->id;
                $dataASLModel->user_id  = $userId;
                $dataASLModel->status  =  $request->status;
                $dataASLModel->patient_id  = $patient_id;
                $dataASLModel->created_at = $timeStamp;
                $dataASLModel->updated_at = $timeStamp;
                $qResponceApp = $dataASLModel->save();
                if (!$qResponceApp) {
                    DB::rollBack();
                    return Helpers::errorResponse("error");
                }
                DB::commit();
                if ($request->status == "Rejected") {
                    if ($appointmentData->type == "Video Consultant") {
                        // Pass dependencies if needed
                        $zoomController = new ZoomVideoCallController();
                        $zoomController->deleteMeeting($appointmentData->id, $appointmentData->meeting_id); // Ensure it doesn't rely on constructor dependencies                 
                    }
                }
                $notificationCentralController = new NotificationCentralController();
                $notificationCentralController->sendAppointmentSatusChangeNotificationToUsers($request->id, $request->status);
                return Helpers::successResponse("successfully");
            } else {
                DB::rollBack();

                return Helpers::errorResponse("error");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return Helpers::errorResponse("error");
        }
    }


    //Delete Data
    function deleteData(Request $request)
    {

        $validator = Validator::make(request()->all(), [
            'id' => 'required'
        ]);
        if ($validator->fails())
            return response(["response" => 400], 400);
        try {

            $dataModel = AppointmentModel::where("id", $request->id)->first();

            $qResponce = $dataModel->delete();
            if ($qResponce) {

                return Helpers::successResponse("successfully");
            } else {

                return Helpers::errorResponse("error");
            }
        } catch (\Exception $e) {
            return Helpers::errorResponse("error");
        }
    }

    // get data

    function getBookedTimeSlotsByDoctIdAndDateAndTpe(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'date' => 'required',
            'doct_id' => 'required',
            'type' => 'required'


        ]);
        if ($validator->fails())
            return response(["response" => 400], 400);

        $data = DB::table("appointments")
            ->select(
                DB::raw('TIME_FORMAT(appointments.time_slots, "%H:%i") as time_slots'),
                'appointments.date',
                'appointments.type',
                'appointments.id as appointment_id'
            )
            ->where("appointments.status", "!=", 'Rejected')
            ->where("appointments.status", "!=", 'Completed')
            ->where("appointments.status", "!=", 'Cancelled')
            ->where("appointments.date", "=", $request->date)
            ->where("appointments.type", "=", $request->type)
            ->where("appointments.doct_id", "=", $request->doct_id)
            ->get();

        $response = [
            "response" => 200,
            'data' => $data,
        ];

        return response($response, 200);
    }

    // get data by id

    function getDataById($id)
    {
        $data = DB::table("appointments")
            ->select(
                'appointments.*',
                'patients.user_id',
                'patients.f_name as patient_f_name',
                'patients.dob as patient_dob',
                'patients.l_name as patient_l_name',
                'patients.l_name as patient_l_name',
                'patients.phone as patient_phone',
                'patients.gender as patient_gender',
                'department.title as dept_title',
                'users.f_name as doct_f_name',
                'users.l_name as doct_l_name',
                "users.image as doct_image",
                "doctors.specialization as doct_specialization",
                // ADD OUT CALL FIELDS
                "doctors.out_call_appointment",
                "doctors.out_call_fee"
            )
            ->Join('patients', 'patients.id', '=', 'appointments.patient_id')
            ->Join('department', 'department.id', '=', 'appointments.dept_id')
            ->Join('users', 'users.id', '=', 'appointments.doct_id')
            ->LeftJoin('doctors', 'doctors.user_id', '=', 'appointments.doct_id')
            ->where('appointments.id', '=', $id)
            ->first();

        if ($data != null) {

            $dataDR = DB::table("doctors_review")
                ->select('doctors_review.*')
                ->where("doctors_review.doctor_id", "=", $data->doct_id)
                ->get();
            // Calculate the total review points
            $totalReviewPoints = $dataDR->sum('points'); // Assuming 'review_points' is the column name for review points

            // Count the number of reviews
            $numberOfReviews = $dataDR->count();
            // Calculate the average rating
            $averageRating = $numberOfReviews > 0 ? number_format($totalReviewPoints / $numberOfReviews, 2) : '0.00';

            $data->total_review_points = $totalReviewPoints;
            $data->number_of_reviews = $numberOfReviews;
            $data->average_rating = $averageRating;

            $dataDApp = DB::table("appointments")
                ->select('appointments.*')
                ->where("appointments.doct_id", "=", $data->doct_id)
                ->get();
            // Calculate the total review points
            $data->total_appointment_done = count($dataDApp);
        }
        $response = [
            "response" => 200,
            'data' => $data,
        ];

        return response($response, 200);
    }

    function getDataByUIDId($id)
    {
        $data = DB::table("appointments")
            ->select(
                'appointments.*',
                'patients.user_id',
                'patients.f_name as patient_f_name',
                'patients.l_name as patient_l_name',
                'department.title as dept_title',
                'users.f_name as doct_f_name',
                'users.l_name as doct_l_name',
                "users.image as doct_image",
                "doctors.specialization as doct_specialization"

            )
            ->Join('patients', 'patients.id', '=', 'appointments.patient_id')
            ->Join('department', 'department.id', '=', 'appointments.dept_id')
            ->Join('users', 'users.id', '=', 'appointments.doct_id')
            ->LeftJoin('doctors', 'doctors.user_id', '=', 'appointments.doct_id')
            ->where('patients.user_id', '=', $id)
            ->orderBy("appointments.date", "DESC")
            ->orderBy("appointments.time_slots", "DESC")
            ->get();

        $response = [
            "response" => 200,
            'data' => $data,
        ];

        return response($response, 200);
    }
    function getDataByPatientId($patientId)
    {
        $data = DB::table("appointments")
            ->select(
                'appointments.*',
                'patients.user_id',
                'patients.f_name as patient_f_name',
                'patients.l_name as patient_l_name',
                'department.title as dept_title',
                'users.f_name as doct_f_name',
                'users.l_name as doct_l_name',
                'users.image as doct_image',
                'doctors.specialization as doct_specialization'
            )
            ->join('patients', 'patients.id', '=', 'appointments.patient_id')
            ->join('department', 'department.id', '=', 'appointments.dept_id')
            ->join('users', 'users.id', '=', 'appointments.doct_id')
            ->leftJoin('doctors', 'doctors.user_id', '=', 'appointments.doct_id')
            ->where('appointments.patient_id', '=', $patientId)
            ->orderBy('appointments.date', 'DESC')
            ->orderBy('appointments.time_slots', 'DESC')
            ->get();

        $response = [
            "response" => 200,
            'data' => $data,
        ];

        return response($response, 200);
    }

    function getDataByDoctorId($id)
    {
        $data = DB::table("appointments")
            ->select(
                'appointments.*',
                'patients.user_id',
                'patients.f_name as patient_f_name',
                'patients.l_name as patient_l_name',
                'department.title as dept_title',
                'users.f_name as doct_f_name',
                'users.l_name as doct_l_name',
                "users.image as doct_image",
                "doctors.specialization as doct_specialization"

            )
            ->Join('patients', 'patients.id', '=', 'appointments.patient_id')
            ->Join('department', 'department.id', '=', 'appointments.dept_id')
            ->Join('users', 'users.id', '=', 'appointments.doct_id')
            ->LeftJoin('doctors', 'doctors.user_id', '=', 'appointments.doct_id')
            ->where('appointments.doct_id', '=', $id)
            ->orderby('appointments.date', "DESC")
            ->orderby('appointments.time_slots', "DESC")
            ->get();

        $response = [
            "response" => 200,
            'data' => $data,
        ];

        return response($response, 200);
    }

    function getData()
    {
        $data = DB::table("appointments")
            ->select(
                'appointments.*',
                'patients.user_id',
                'patients.f_name as patient_f_name',
                'patients.l_name as patient_l_name',
                'patients.phone as patient_phone',
                'department.title as dept_title',
                'users.f_name as doct_f_name',
                'users.l_name as doct_l_name',
                "users.image as doct_image",
                "doctors.specialization as doct_specialization",
                 // ADD OUT CALL FIELDS
                "appointments.out_call_address",
                "appointments.out_call_city", 
                "appointments.out_call_landmark",
                "appointments.out_call_instructions"

            )
            ->Join('patients', 'patients.id', '=', 'appointments.patient_id')
            ->Join('department', 'department.id', '=', 'appointments.dept_id')
            ->Join('users', 'users.id', '=', 'appointments.doct_id')
            ->LeftJoin('doctors', 'doctors.user_id', '=', 'appointments.doct_id')
            ->orderBy("appointments.date", "DESC")
            ->orderBy("appointments.time_slots", "DESC")
            ->get();

        $response = [
            "response" => 200,
            'data' => $data,
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
            return response(["response" => 400], 400);

        // Query the appointments table with the date range
        $data = DB::table("appointments")
            ->select(
                'appointments.*',
                'patients.user_id',
                'patients.f_name as patient_f_name',
                'patients.l_name as patient_l_name',
                'patients.phone as patient_phone',
                'department.title as dept_title',
                'users.f_name as doct_f_name',
                'users.l_name as doct_l_name',
                'users.image as doct_image',
                'doctors.specialization as doct_specialization'
            )
            ->join('patients', 'patients.id', '=', 'appointments.patient_id')
            ->join('department', 'department.id', '=', 'appointments.dept_id')
            ->join('users', 'users.id', '=', 'appointments.doct_id')
            ->leftJoin('doctors', 'doctors.user_id', '=', 'appointments.doct_id')
            ->whereBetween('appointments.date', [$startDate, $endDate])
            ->orderBy("appointments.date", "DESC")
            ->orderBy("appointments.time_slots", "DESC")
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

        $query = DB::table("appointments")
            ->select(
                'appointments.*',
                'patients.user_id',
                'patients.f_name as patient_f_name',
                'patients.l_name as patient_l_name',
                'patients.phone as patient_phone',
                'department.title as dept_title',
                'users.f_name as doct_f_name',
                'users.l_name as doct_l_name',
                "users.image as doct_image",
                "doctors.specialization as doct_specialization"

            )
            ->Join('patients', 'patients.id', '=', 'appointments.patient_id')
            ->Join('department', 'department.id', '=', 'appointments.dept_id')
            ->Join('users', 'users.id', '=', 'appointments.doct_id')
            ->LeftJoin('doctors', 'doctors.user_id', '=', 'appointments.doct_id')
            ->orderBy("appointments.date", "DESC")
            ->orderBy("appointments.time_slots", "DESC");


            if (!empty($request->start_date)) {
                $query->whereDate('appointments.date', '>=', $request->start_date);
            }
            
            if (!empty($request->end_date)) {
                $query->whereDate('appointments.date', '<=', $request->end_date);
            }

            // Filter by multiple statuses if provided
        if (!empty($request->status)) {
            $status = explode(',', $request->status); // Convert comma-separated string into an array
            $query->whereIn('appointments.status', $status);
            }
            
        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('patients.user_id', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('CONCAT(patients.f_name, " ", patients.l_name)'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('CONCAT(users.f_name, " ", users.l_name)'), 'like', '%' . $search . '%')
                    ->orWhere('patients.phone', 'like', '%' . $search . '%')
                    ->orWhere('department.title', 'like', '%' . $search . '%')
                    ->orWhere('appointments.id', 'like', '%' . $search . '%')
                    ->orWhere('appointments.status', 'like', '%' . $search . '%')
                    ->orWhere('appointments.time_slots', 'like', '%' . $search . '%')
                    ->orWhere('appointments.date', 'like', '%' . $search . '%')
                    ->orWhere('appointments.type', 'like', '%' . $search . '%')
                    ->orWhere('appointments.meeting_id', 'like', '%' . $search . '%')
                    ->orWhere('appointments.payment_status', 'like', '%' . $search . '%')
                    ->orWhere('appointments.current_cancel_req_status', 'like', '%' . $search . '%')
                    ->orWhere('doctors.specialization', 'like', '%' . $search . '%');
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

    public function getDataPegDrId(Request $request)
    {

        // Calculate the limit
        $start = $request->start;
        $end = $request->end;
        $limit = ($end - $start);
        if (!isset($request->doctor_id)) {
            return response(["response" => 400], 400);
        }

        // Define the base query

        $query = DB::table("appointments")
            ->select(
                'appointments.*',
                'patients.user_id',
                'patients.f_name as patient_f_name',
                'patients.l_name as patient_l_name',
                'patients.phone as patient_phone',
                'department.title as dept_title',
                'users.f_name as doct_f_name',
                'users.l_name as doct_l_name',
                "users.image as doct_image",
                "doctors.specialization as doct_specialization"

            )
            ->Join('patients', 'patients.id', '=', 'appointments.patient_id')
            ->Join('department', 'department.id', '=', 'appointments.dept_id')
            ->Join('users', 'users.id', '=', 'appointments.doct_id')
            ->LeftJoin('doctors', 'doctors.user_id', '=', 'appointments.doct_id')
            ->where("appointments.doct_id", $request->doctor_id)
            ->orderBy("appointments.date", "DESC")
            ->orderBy("appointments.time_slots", "DESC");
            if (!empty($request->start_date)) {
                $query->whereDate('appointments.date', '>=', $request->start_date);
            }
            
            if (!empty($request->end_date)) {
                $query->whereDate('appointments.date', '<=', $request->end_date);
            }
            if (!empty($request->status)) {
                $status = explode(',', $request->status); // Convert comma-separated string into an array
                $query->whereIn('appointments.status', $status);
                }

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('patients.user_id', 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('CONCAT(patients.f_name, " ", patients.l_name)'), 'like', '%' . $search . '%')
                    ->orWhere(DB::raw('CONCAT(users.f_name, " ", users.l_name)'), 'like', '%' . $search . '%')
                    ->orWhere('patients.phone', 'like', '%' . $search . '%')
                    ->orWhere('department.title', 'like', '%' . $search . '%')
                    ->orWhere('appointments.id', 'like', '%' . $search . '%')
                    ->orWhere('appointments.status', 'like', '%' . $search . '%')
                    ->orWhere('appointments.time_slots', 'like', '%' . $search . '%')
                    ->orWhere('appointments.date', 'like', '%' . $search . '%')
                    ->orWhere('appointments.type', 'like', '%' . $search . '%')
                    ->orWhere('appointments.meeting_id', 'like', '%' . $search . '%')
                    ->orWhere('appointments.payment_status', 'like', '%' . $search . '%')
                    ->orWhere('appointments.current_cancel_req_status', 'like', '%' . $search . '%')
                    ->orWhere('doctors.specialization', 'like', '%' . $search . '%');
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
}
