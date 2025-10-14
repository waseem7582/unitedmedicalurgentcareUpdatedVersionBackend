<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\PrescriptionController;
use App\Http\Controllers\Api\V1\AppointmentInvoiceController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
    // return view('installation.step6');
    
});
Route::get('prescriptions/pdf/{id}', [PrescriptionController::class, 'generatePDF']);
Route::get('invoice/pdf/{id}', [AppointmentInvoiceController::class, 'generatePDF']);

