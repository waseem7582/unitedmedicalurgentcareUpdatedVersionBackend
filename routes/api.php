<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\LoginController;
use App\Http\Controllers\Api\V1\DepartmentController;
use App\Http\Controllers\Api\V1\SpecializationController;
use App\Http\Controllers\Api\V1\DoctorController;
use App\Http\Controllers\Api\V1\TimeSlotsController;
use App\Http\Controllers\Api\V1\TimeSlotsVideoController;
use App\Http\Controllers\Api\V1\PatientController;
use App\Http\Controllers\Api\V1\AppointmentController;
use App\Http\Controllers\Api\V1\ProductCatController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\AddressController;
use App\Http\Controllers\Api\V1\AllowPincodeController;
use App\Http\Controllers\Api\V1\OrderController;
use App\Http\Controllers\Api\V1\LabTestController;
use App\Http\Controllers\Api\V1\LabReqController;
use App\Http\Controllers\Api\V1\DoctorsReviewController;
use App\Http\Controllers\Api\V1\AppointmentCancellationRedController;
use App\Http\Controllers\Api\V1\PrescribeMedicinesController;
use App\Http\Controllers\Api\V1\PrescriptionController;
use App\Http\Controllers\Api\V1\AllTransactionController;
use App\Http\Controllers\Api\V1\AppointmentInvoiceController;
use App\Http\Controllers\Api\V1\AppointmentPaymentController;
use App\Http\Controllers\Api\V1\RazorpayController;
use App\Http\Controllers\Api\V1\ZoomVideoCallController;
use App\Http\Controllers\Api\V1\FamilyMembersController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\RoleAssignController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\RolePermissionController;
use App\Http\Controllers\Api\V1\LoginScreenController;
use App\Http\Controllers\Api\V1\WebPageController;
use App\Http\Controllers\Api\V1\SocialMediaController;
use App\Http\Controllers\Api\V1\ConfigurationsController;
use App\Http\Controllers\Api\V1\TestimonialController;
use App\Http\Controllers\Api\V1\SendNotificationController;
use App\Http\Controllers\Api\V1\StorageLinkController;
use App\Http\Controllers\Api\V1\UserNotificationController;
use App\Http\Controllers\Api\V1\DoctorNotificationController;
use App\Http\Controllers\Api\V1\StripeController;
use App\Http\Controllers\Api\V1\VitalsMeasurementsController;
use App\Http\Controllers\Api\V1\CouponController;
use App\Http\Controllers\Api\V1\CouponUseController;
use App\Http\Controllers\Api\V1\AppointmentCheckinController;
use App\Http\Controllers\Api\V1\RazorpayPaymentController;
use App\Http\Controllers\Api\V1\WebhookController;
use App\Http\Controllers\Api\V1\PaymentGatewayController;
use App\Http\Controllers\Api\V1\PatientFilesController;
use App\Http\Controllers\Api\V1\AdminNotificationController;
use App\Http\Controllers\Api\V1\AppointmentStatusLogController;
use App\Http\Controllers\Api\V1\SmtpController;
use App\Http\Controllers\Api\V1\ContactFormInboxController;
use App\Http\Controllers\Api\V1\DoctorTrackingController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['prefix' => 'v1', 'namespace' => 'api\v1', 'middleware' => 'auth:sanctum'], function () {

    //Users

    Route::post("update_password", [UserController::class, 'updatePassword']);
    Route::post("remove_user_image", [UserController::class, 'removeImage']);
    Route::post("update_user", [UserController::class, 'updateDetails']);
    Route::post("user_soft_delete", [UserController::class, 'updateDeleted']);

    //Login
    Route::post("logout", [LoginController::class, 'logout']);



    //Role
    Route::post("add_role", [RoleController::class, 'addData']);
    Route::post("update_role", [RoleController::class, 'updateData']);
    Route::post("delete_role", [RoleController::class, 'deleteData']);

    //RoleAssign
    Route::post("assign_role", [RoleAssignController::class, 'addData']);
    Route::post("de_assign_role", [RoleAssignController::class, 'deleteData']);

    //RolePermission
    Route::post("assign_permission_to_tole", [RolePermissionController::class, 'addData']);
    // Route::post("de_assign_role",[RolePermissionController::class,'deleteData']);

    //Department
    Route::post("add_department", [DepartmentController::class, 'addData']);
    Route::post("udpate_department", [DepartmentController::class, 'updateData']);
    Route::post("remove_department_image", [DepartmentController::class, 'removeImage']);
    Route::post("delete_department", [DepartmentController::class, 'deleteData']);


    //Specialization
    Route::post("add_specialization", [SpecializationController::class, 'addData']);
    Route::post("update_specialization", [SpecializationController::class, 'updateData']);
    Route::post("delete_specialization", [SpecializationController::class, 'deleteData']);






    //Doctors
    Route::post("add_doctor", [DoctorController::class, 'addData']);
    Route::post("remove_doctor_image", [DoctorController::class, 'removeImage']);
    Route::post("update_doctor", [DoctorController::class, 'updateData']);

    //Doctors Review 
    Route::post("add_doctor_review", [DoctorsReviewController::class, 'addData']);

    // Certificate upload requires authentication
    Route::post("upload_doctor_certificate", [DoctorController::class, 'uploadCertificate']);

    //Patients
    Route::post("add_patient", [PatientController::class, 'addData']);
    Route::post("update_patient", [PatientController::class, 'updateData']);
    Route::post("remove_patient_image", [PatientController::class, 'removeImage']);

    //TimeSlots
    Route::post("add_timeslots", [TimeSlotsController::class, 'addData']);
    Route::post("delete_timeslots", [TimeSlotsController::class, 'deleteData']);



    //Video TimeSlots
    Route::post("add_video_timeslots", [TimeSlotsVideoController::class, 'addData']);
    Route::post("delete_video_timeslots", [TimeSlotsVideoController::class, 'deleteData']);


    //Appointments
    Route::post("add_appointment", [AppointmentController::class, 'addData']);
    Route::post("update_appointment_status", [AppointmentController::class, 'updateStatus']);
    Route::post("appointment_rescheduled", [AppointmentController::class, 'appointmentResch']);
    Route::post("update_appointment_to_paid", [AppointmentController::class, 'updateStatusToPaid']);

    
    //AppointmentStatusLogController
    Route::get("get_appointment_status_log", [AppointmentStatusLogController::class, 'getData']);
    Route::get("get_appointment_status_log/{id}", [AppointmentStatusLogController::class, 'getDataById']);
    Route::get("get_appointment_status_log_page", [AppointmentStatusLogController::class, 'getDataPeg']);


    //Appointment Cancellation
    Route::post("appointment_cancellation", [AppointmentCancellationRedController::class, 'addData']);
    Route::post("delete_appointment_cancellation", [AppointmentCancellationRedController::class, 'deleteDataByUser']);
    Route::post("delete_appointment_cancellation_by_admin", [AppointmentCancellationRedController::class, 'deleteData']);
    Route::post("appointment_cancellation_and_refund", [AppointmentCancellationRedController::class, 'cancleAndRefund']);
    Route::post("appointment_reject_and_refund", [AppointmentCancellationRedController::class, 'RejectAndRefund']);





    //Prescribe Medicines
    Route::post("add_prescribe_medicines", [PrescribeMedicinesController::class, 'addData']);
    Route::post("update_prescribe_medicines", [PrescribeMedicinesController::class, 'updateData']);
    Route::post("delete_prescribe_medicines", [PrescribeMedicinesController::class, 'deleteData']);
  

    
    //Prescription
    Route::post("add_prescription", [PrescriptionController::class, 'addData']);
    Route::post("update_prescription", [PrescriptionController::class, 'updateData']);
    Route::post("delete_prescription", [PrescriptionController::class, 'deleteData']);
    Route::post("upload_prescription", [PrescriptionController::class, 'uploadPdfAndJson']);

    //Stripe
    Route::post('create_intent', [StripeController::class, 'createIntent']);

    //AllTransaction
    Route::post("add_wallet_money", [AllTransactionController::class, 'updateWalletMoneyData']);

    //ZoomVideoCall
    Route::post("create_zoom_meeting", [ZoomVideoCallController::class, 'createMeeting']);

    //Patient
    Route::post("add_family_member", [FamilyMembersController::class, 'addData']);
    Route::post("delete_family_member", [FamilyMembersController::class, 'deleteData']);
    Route::post("update_family_member", [FamilyMembersController::class, 'updateData']);

    //LoginScreen
    Route::post("add_login_screen_image", [LoginScreenController::class, 'addData']);
    Route::post("update_login_screen_image", [LoginScreenController::class, 'updateData']);
    Route::post("delete_login_screen_image", [LoginScreenController::class, 'deleteData']);

    //Testimonial
    Route::post("add_testimonial", [TestimonialController::class, 'addData']);
    Route::post("update_testimonial", [TestimonialController::class, 'updateData']);
    Route::post("delete_testimonial", [TestimonialController::class, 'deleteData']);
    Route::post('remove_testimonia_image', [TestimonialController::class, 'removeImage']);

    //SocialMedia
    Route::post('add_social_media', [SocialMediaController::class, 'addData']);
    Route::post('delete_social_media', [SocialMediaController::class, 'deleteData']);
    Route::post('update_social_media', [SocialMediaController::class, 'updateData']);
    Route::post('remove_social_media_image', [SocialMediaController::class, 'removeImage']);



    //WebPage
    Route::post("update_web_page", [WebPageController::class, 'updateData']);

    //Configurations
    Route::post("update_configurations", [ConfigurationsController::class, 'updateData']);
    Route::post("remove_configurations_image", [ConfigurationsController::class, 'removeImage']);

    //SendNotification
    Route::post("sent_notificaiton_to_token", [SendNotificationController::class, 'sendReqFirebaseNotificationToToken']);
    Route::post("sent_notificaiton_to_topic", [SendNotificationController::class, 'sendReqFirebaseNotificationToTopic']);
    Route::post("subscribe_to_topic", [SendNotificationController::class, 'subscribeToTopic']);



    //UserNotificationController
    Route::post("add_user_notification", [UserNotificationController::class, 'addData']);
    Route::post("delete_notification", [UserNotificationController::class, 'deleteData']);

    //DoctorNotification
    Route::post("delete_doctor_notification", [DoctorNotificationController::class, 'deleteData']);

    //VitalsMeasurementsController
    Route::post("add_vitals", [VitalsMeasurementsController::class, 'addData']);
    Route::post("delete_vitals", [VitalsMeasurementsController::class, 'deleteData']);
    Route::post("update_vitals", [VitalsMeasurementsController::class, 'updateData']);

    //Coupon
    Route::post("add_coupon", [CouponController::class, 'addData']);
    Route::post("update_coupon", [CouponController::class, 'updateDetails']);
    Route::post("delete_coupon", [CouponController::class, 'deleteData']);
    Route::post("get_validate", [CouponController::class, 'getValidate']);

    //CouponUse
    Route::post("delete_coupon_use", [CouponUseController::class, 'deleteData']);

    //AppointmentCheckIn
    Route::post("add_appointment_checkin", [AppointmentCheckinController::class, 'addData']);
    Route::post("delete_appointment_checkin", [AppointmentCheckinController::class, 'deleteData']);
    Route::post("update_appointment_checkin", [AppointmentCheckinController::class, 'updateDetails']);

    //RazorpayPayment
    Route::post("create_rz_order", [RazorpayPaymentController::class, 'createOrder']);

    //PaymentGateway 
    Route::post("update_payment_gateway", [PaymentGatewayController::class, 'updateData']);

    //PatientFiles
    Route::post("add_patient_file", [PatientFilesController::class, 'addData']);
    Route::post("update_patient_file", [PatientFilesController::class, 'updateData']);
    Route::post("delete_patient_file", [PatientFilesController::class, 'deleteData']);

    Route::post("delete_contact_us_form_data", [ContactFormInboxController::class, 'deleteData']);
});

// Add these routes - Doctor Tracking APIs
Route::group(['prefix' => 'v1/tracking', 'middleware' => 'auth:api'], function () {
    Route::post('update-location', [DoctorTrackingController::class, 'updateDoctorLocation']);
    Route::post('mark-arrived', [DoctorTrackingController::class, 'markAsArrived']);
    Route::post('mark-completed', [DoctorTrackingController::class, 'markAsCompleted']);
    Route::get('doctor-history', [DoctorTrackingController::class, 'getDoctorTrackingHistory']);
});


Route::group(['prefix' => 'v1', 'namespace' => 'api\v1'], function () {
    Route::post("login", [LoginController::class, 'login']);
    Route::post("login_phone", [LoginController::class, 'loginMobile']);
    Route::post("re_login_phone", [LoginController::class, 'ReLoginMobile']);
   
    
    //Specialization
    Route::get("get_specialization", [SpecializationController::class, 'getData']);
    Route::get("get_specialization/{id}", [SpecializationController::class, 'getDataById']);




    //Department
    Route::get("get_department", [DepartmentController::class, 'getData']);
    Route::get("get_department_active", [DepartmentController::class, 'getDataActive']);

    Route::get("get_department/{id}", [DepartmentController::class, 'getDataById']);



    //Doctors
    Route::get("get_doctor", [DoctorController::class, 'getData']);
    Route::get("get_doctor/{id}", [DoctorController::class, 'getDataById']);
    Route::get("get_doctor_dep_id/{id}", [DoctorController::class, 'getDataByDepId']);
    Route::get("get_active_doctor", [DoctorController::class, 'getDataActive']);

    //Doctors Review 
    Route::get("get_doctor_review/doctor/{id}", [DoctorsReviewController::class, 'getDataByDoctorId']);
    Route::get("get_all_doctor_review", [DoctorsReviewController::class, 'getData']);
    Route::get("get_doctor_review_page", [DoctorsReviewController::class, 'getDataPeg']);

     // QR code generation and verification should be publicly accessible
    Route::get("get_qr_code_data/{doctorId}", [DoctorController::class, 'getQRCodeData']); // Generate QR code data
    Route::get("verify-doctor/{token}", [DoctorController::class, 'verifyDoctor']); // Verify doctor via QR code
    // Public route for patient to get tracking info
    Route::get('v1/get-tracking-info/{appointment_id}', [DoctorTrackingController::class, 'getTrackingInfo']);
    
    //Patients
    Route::get("get_patients", [PatientController::class, 'getData']);
    Route::get("get_patients/{id}", [PatientController::class, 'getDataById']);
    Route::get("get_patients/user/{id}", [PatientController::class, 'getDataByUID']);
    Route::get("get_patients_date", [PatientController::class, 'getDataByDate']);
    Route::get("get_patient/page", [PatientController::class, 'getDataPeg']);

    //Role
    Route::get("get_roles", [RoleController::class, 'getData']);
    Route::get("get_roles/{id}", [RoleController::class, 'getDataById']);


    //RoleAssign
    Route::get("get_assign_roles", [RoleAssignController::class, 'getData']);
    Route::get("get_assign_roles/{id}", [RoleAssignController::class, 'getDataById']);

    //Permission
    Route::get("get_permisssion", [PermissionController::class, 'getData']);
    Route::get("get_permisssion/{id}", [PermissionController::class, 'getDataById']);

    //RolePermission
    Route::get("get_role_permisssion", [RolePermissionController::class, 'getData']);
    Route::get("get_role_permisssion/{id}", [RolePermissionController::class, 'getDataById']);
    Route::get("get_role_permisssion/role/{id}", [RolePermissionController::class, 'getDataByRoleId']);

    //Time Slots
    Route::get("get_doctor_time_slots/{id}", [TimeSlotsController::class, 'getDataByDoctId']);
    Route::get("get_doctor_time_interval/{id}/{day}", [TimeSlotsController::class, 'getDataDoctotToimeInterval']);


    //Time Slots Video
    Route::get("get_doctor_video_time_slots/{id}", [TimeSlotsVideoController::class, 'getDataByDoctId']);
    Route::get("get_doctor_video_time_interval/{id}/{day}", [TimeSlotsVideoController::class, 'getDataDoctotToimeInterval']);



    //Appointment
    Route::get("get_appointments", [AppointmentController::class, 'getData']);
    Route::get("get_appointment/user/{id}", [AppointmentController::class, 'getDataByUIDId']);
    Route::get("get_appointment/patient/{id}", [AppointmentController::class, 'getDataByPatientId']);
    Route::get("get_appointment/{id}", [AppointmentController::class, 'getDataById']);
    Route::get("get_appointment_date", [AppointmentController::class, 'getDataByDate']);
    Route::get("get_booked_time_slots", [AppointmentController::class, 'getBookedTimeSlotsByDoctIdAndDateAndTpe']);
    Route::get("get_appointments/page", [AppointmentController::class, 'getDataPeg']);
    Route::get("get_appointments/doctor/{id}", [AppointmentController::class, 'getDataByDoctorId']);
    Route::get("get_appointments/doctor_id/page", [AppointmentController::class, 'getDataPegDrId']);



    //Appointment Cancellation
    Route::get("get_appointment_cancel_req/appointment/{id}", [AppointmentCancellationRedController::class, 'getDataByAppointmentId']);


    //users
    Route::get("get_user/{id}", [UserController::class, 'getDataById']);
    Route::get("get_users", [UserController::class, 'getData']);
    Route::get("get_users_date", [UserController::class, 'getDataByDate']);
    Route::post("add_user", [UserController::class, 'addData']);
    Route::get("get_users/page", [UserController::class, 'getDataPeg']);


    //Prescribe Medicines
    Route::get("get_prescribe_medicines/{id}", [PrescribeMedicinesController::class, 'getDataById']);
    Route::get("get_prescribe_medicines", [PrescribeMedicinesController::class, 'getData']);

    // prescription
    Route::get("get_prescription", [PrescriptionController::class, 'getData']);

    Route::get("get_prescription_page", [PrescriptionController::class, 'getDataPeg']);
    Route::get("prescription/generatePDF/{id}", [PrescriptionController::class, 'generatePDF']);
    Route::get("consultation_report/{id}", [PrescriptionController::class, 'generate_blank_prescriptionsPDF']);
    Route::get("get_prescription/{id}", [PrescriptionController::class, 'getDataById']);
    Route::get("get_prescription/appointment/{id}", [PrescriptionController::class, 'getDataByAppointmentId']);
    Route::get("get_prescription/doctor/{id}", [PrescriptionController::class, 'getDataBydoctrId']);
    Route::get("get_prescription/user/{id}", [PrescriptionController::class, 'getDataByUID']);
    Route::get("get_prescription/patient/{id}", [PrescriptionController::class, 'getDataByPatientId']);
    Route::get("get_prescription_doctor_search", [PrescriptionController::class, 'getDataBydoctrIdSearch']);



    //AllTransaction
    Route::get("get_all_transaction", [AllTransactionController::class, 'getData']);
    Route::get("get_all_transaction/{id}", [AllTransactionController::class, 'getDataById']);
    Route::get("get_all_transaction/appointment/{id}", [AllTransactionController::class, 'getDataByAppId']);
    Route::get("get_wallet_txn/user/{id}", [AllTransactionController::class, 'getDataWalletTxnByUI']);
    Route::get("get_all_transaction_date", [AllTransactionController::class, 'getDataByDate']);
    Route::get("get_all_transactions/page", [AllTransactionController::class, 'getDataPeg']);
    //AppointmentInvoice
    Route::get("get_invoice", [AppointmentInvoiceController::class, 'getData']);
    Route::get("get_invoice/{id}", [AppointmentInvoiceController::class, 'getDataById']);
    Route::get("get_invoice/appointment/{id}", [AppointmentInvoiceController::class, 'getDataByAppId']);
    Route::get("invoice/generatePDF/{id}", [AppointmentInvoiceController::class, 'generatePDF']);
    Route::get("get_invoices/page", [AppointmentInvoiceController::class, 'getDataPeg']);

    //AppointmentPaymentController
    Route::get("get_appointment_payment", [AppointmentPaymentController::class, 'getData']);
    Route::get("get_appointment_payment/{id}", [AppointmentPaymentController::class, 'getDataById']);
    Route::get("get_appointment_payment/appintment/{id}", [AppointmentPaymentController::class, 'getDataByAppId']);
    Route::get("get_appointment_payments/page", [AppointmentPaymentController::class, 'getDataPeg']);
    //FamilyMembers

    Route::get("get_family_members", [FamilyMembersController::class, 'getData']);
    Route::get("get_family_members/user/{id}", [FamilyMembersController::class, 'getDataByUserId']);
    Route::get("get_family_members/{id}", [FamilyMembersController::class, 'getDataById']);
    Route::get("get_family_member/page", [FamilyMembersController::class, 'getDataPeg']);


    //Dashboard
    Route::get("get_dashboard_count", [DashboardController::class, 'getDataDashBoardCount']);
    Route::get("get_dashboard_count/doctor/{id}", [DashboardController::class, 'getDataDashBoardCountByDoctor']);

    //LoginScreenController
    Route::get("get_login_screen_images", [LoginScreenController::class, 'getData']);
    Route::get("get_login_screen_images/{id}", [LoginScreenController::class, 'getDataById']);

    //SocialMedia
    Route::get("get_social_media", [SocialMediaController::class, 'getDataAllData']);

    //WebPage
    Route::get("get_web_pages", [WebPageController::class, 'getData']);
    Route::get("get_web_page/page/{id}", [WebPageController::class, 'getDataByPageId']);

    //Configurations
    Route::get("get_configurations", [ConfigurationsController::class, 'getData']);
    Route::get("get_configurations/{id}", [ConfigurationsController::class, 'getDataById']);
    Route::get("get_configurations/id_name/{id_name}", [ConfigurationsController::class, 'getDataByIdName']);
    Route::get("get_configurations/group_name/{group_name}", [ConfigurationsController::class, 'getDataByGroupName']);
    Route::get("get_configurations_all", [ConfigurationsController::class, 'getDataAll']);


    //Testimonial
    Route::get("get_testimonial", [TestimonialController::class, 'getData']);
    Route::get("get_testimonial/{id}", [TestimonialController::class, 'getDataById']);

    //StorageLink
    Route::get("storage_link", [StorageLinkController::class, 'createSymbolicLink']);

    //UserNotification
    Route::get("get_user_notification", [UserNotificationController::class, 'getData']);
    Route::get("get_user_notification_page", [UserNotificationController::class, 'getDataPeg']);
    Route::get("get_user_notification/{id}", [UserNotificationController::class, 'getDataById']);
    Route::get("get_user_notification/date/{uid}/{date}", [UserNotificationController::class, 'getDataByDate']);
    Route::get("users_notification_seen_status/{id}", [UserNotificationController::class, 'checkNotificaitonSeen']);

    //DoctorNotification
    Route::get("get_doctor_notification", [DoctorNotificationController::class, 'getData']);
    Route::get("get_doctor_notification_page", [DoctorNotificationController::class, 'getDataPeg']);
    Route::get("get_doctor_notification/{id}", [DoctorNotificationController::class, 'getDataById']);
    Route::get("doctor_notification_seen_status/{id}", [DoctorNotificationController::class, 'checkNotificaitonSeen']);
    Route::get("get_doctor_notification/doctor/{id}", [DoctorNotificationController::class, 'getDataByDoctorId']);
    

    //AdminNotification
    Route::get("get_admin_notification", [AdminNotificationController::class, 'getData']);
    Route::get("get_admin_notification_page", [AdminNotificationController::class, 'getDataPeg']);

    Route::get("get_admin_notification/{id}", [AdminNotificationController::class, 'getDataById']);


    //VitalsMeasurements
    Route::get("get_vitals", [VitalsMeasurementsController::class, 'getData']);
    Route::get("get_vitals/{id}", [VitalsMeasurementsController::class, 'getDataById']);
    Route::get("get_vitals/user/{id}", [VitalsMeasurementsController::class, 'getDataByUserId']);
    Route::get("get_vitals/family_member/{id}", [VitalsMeasurementsController::class, 'getDataByFamilyMemberId']);
    Route::get("get_vitals_family_member_id_type", [VitalsMeasurementsController::class, 'getDataByFamilyMemberIdType']);

    //Coupon
    Route::get("get_coupon", [CouponController::class, 'getData']);
    Route::get("get_coupon_active", [CouponController::class, 'getDataActive']);
    Route::get("get_coupon/{id}", [CouponController::class, 'getDataById']);

    //CouponUse
    Route::get("get_coupon_use", [CouponUseController::class, 'getData']);
    Route::get("get_coupon_use/{id}", [CouponUseController::class, 'getDataById']);

    //AppointmentCheckIn
    Route::get("get_appointment_check_in", [AppointmentCheckinController::class, 'getData']);
    Route::get("get_appointment_check_in_page", [AppointmentCheckinController::class, 'getDataPeg']);
    Route::get("get_appointment_check_in/{id}", [AppointmentCheckinController::class, 'getDataById']);
    Route::get("get_appointment_check_in_doct_date/{doct_id}/{date}", [AppointmentCheckinController::class, 'getDataByDoctIdAndDate']);

    //WebhookController
    Route::post("test_centreliz_logic", [WebhookController::class, 'centrelizeData']);
    Route::post("rz_webhook", [WebhookController::class, 'handleWebhook']);
    Route::post("stripe_webhook", [WebhookController::class, 'handleWebhookStripe']);


    //PaymentGateway 
    Route::get("get_payment_gateway", [PaymentGatewayController::class, 'getData']);
    Route::get("get_payment_gateway/{id}", [PaymentGatewayController::class, 'getDataById']);
    Route::get("get_payment_gateway_active", [PaymentGatewayController::class, 'getDataByActive']);


    //PatientFiles
    Route::get("get_patient_file", [PatientFilesController::class, 'getData']);

    Route::get("get_patient_file_page", [PatientFilesController::class, 'getDataPeg']);
    Route::get("get_patient_file/{id}", [PatientFilesController::class, 'getDataById']);
    Route::get("get_patient_file/patient/{id}", [PatientFilesController::class, 'getDataByPatientId']);
    Route::get("get_patient_file_q/user", [PatientFilesController::class, 'getDataByUid']);
    Route::get("get_patient_file_q_patient", [PatientFilesController::class, 'getDataByPatientIdSearch']);


    
    Route::post("forget_password", [SmtpController::class, 'sendForgetMail']);

    Route::post("add_contact_us_form_data", [ContactFormInboxController::class, 'addData']);
    Route::get("get_contact_us_form_data", [ContactFormInboxController::class, 'getData']);
    Route::get("get_contact_us_form_data/{id}", [ContactFormInboxController::class, 'getDataByid']);
    


});


