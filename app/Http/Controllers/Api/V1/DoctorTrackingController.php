<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DoctorTrackingModel;
use Illuminate\Support\Facades\Validator;
use App\CentralLogics\Helpers;
use Illuminate\Support\Facades\Log;

class DoctorTrackingController extends Controller
{
    // Update doctor location and auto-start tracking
    public function updateDoctorLocation(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'appointment_id' => 'required|exists:appointments,id',
            'current_lat' => 'required|numeric',
            'current_lng' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response(["response" => 400, "message" => "Invalid location data"], 400);
        }

        try {
            $tracking = DoctorTrackingModel::where('appointment_id', $request->appointment_id)
                ->where('doctor_id', $request->user()->id)
                ->first();

            if (!$tracking) {
                return Helpers::errorResponse("Tracking record not found");
            }

            $updates = [
                'current_lat' => $request->current_lat,
                'current_lng' => $request->current_lng,
            ];

            // Auto-start tracking if not started and doctor is moving
            if ($tracking->tracking_status == 'not_started') {
                $updates['tracking_status'] = 'traveling';
                $updates['tracking_started_at'] = now();
                
                Log::info('Auto-started tracking for appointment: ' . $request->appointment_id);
            }

            // Calculate distance and ETA
            $tracking->fill($updates);
            $distance = $tracking->calculateDistance();
            $eta = $tracking->calculateETA();
            
            if ($distance) {
                $updates['distance_km'] = $distance;
            }
            
            if ($eta) {
                $updates['eta_minutes'] = $eta;
            }

            $tracking->update($updates);

            return response([
                "response" => 200,
                "message" => "Location updated successfully",
                "tracking_status" => $tracking->tracking_status,
                "distance_km" => $tracking->distance_km,
                "eta_minutes" => $tracking->eta_minutes,
                "last_updated" => $tracking->updated_at->format('Y-m-d H:i:s'),
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating doctor location: ' . $e->getMessage());
            return Helpers::errorResponse("Error updating location");
        }
    }

    // Doctor marks as arrived
    public function markAsArrived(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'appointment_id' => 'required|exists:appointments,id',
        ]);

        if ($validator->fails()) {
            return response(["response" => 400], 400);
        }

        try {
            $tracking = DoctorTrackingModel::where('appointment_id', $request->appointment_id)
                ->where('doctor_id', $request->user()->id)
                ->first();

            if ($tracking) {
                $tracking->update([
                    'tracking_status' => 'arrived',
                    'arrived_at' => now(),
                    'eta_minutes' => 0, // ETA is 0 when arrived
                    'distance_km' => 0, // Distance is 0 when arrived
                ]);

                Log::info('Doctor arrived for appointment: ' . $request->appointment_id);

                // TODO: Send push notification to patient

                return Helpers::successResponse("Arrived status updated");
            }

            return Helpers::errorResponse("Tracking record not found");
        } catch (\Exception $e) {
            Log::error('Error marking as arrived: ' . $e->getMessage());
            return Helpers::errorResponse("Error updating arrived status");
        }
    }

    // Doctor marks as completed
    public function markAsCompleted(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'appointment_id' => 'required|exists:appointments,id',
        ]);

        if ($validator->fails()) {
            return response(["response" => 400], 400);
        }

        try {
            $tracking = DoctorTrackingModel::where('appointment_id', $request->appointment_id)
                ->where('doctor_id', $request->user()->id)
                ->first();

            if ($tracking) {
                $tracking->update([
                    'tracking_status' => 'completed',
                    'completed_at' => now(),
                ]);

                Log::info('Appointment completed: ' . $request->appointment_id);

                return Helpers::successResponse("Appointment completed");
            }

            return Helpers::errorResponse("Tracking record not found");
        } catch (\Exception $e) {
            Log::error('Error marking as completed: ' . $e->getMessage());
            return Helpers::errorResponse("Error completing appointment");
        }
    }

    // Get tracking info for patient
    public function getTrackingInfo($appointment_id)
    {
        try {
            $tracking = DoctorTrackingModel::with(['doctor'])
                ->where('appointment_id', $appointment_id)
                ->first();

            if ($tracking) {
                return response([
                    "response" => 200,
                    "tracking_status" => $tracking->tracking_status,
                    "doctor_location" => [
                        'lat' => $tracking->current_lat,
                        'lng' => $tracking->current_lng,
                    ],
                    "patient_location" => [
                        'lat' => $tracking->patient_lat,
                        'lng' => $tracking->patient_lng,
                    ],
                    "distance_km" => $tracking->distance_km,
                    "eta_minutes" => $tracking->eta_minutes,
                    "doctor_name" => $tracking->doctor->f_name . ' ' . $tracking->doctor->l_name,
                    "doctor_phone" => $tracking->doctor->phone,
                    "last_updated" => $tracking->updated_at->format('Y-m-d H:i:s'),
                    "tracking_started_at" => $tracking->tracking_started_at?->format('Y-m-d H:i:s'),
                    "arrived_at" => $tracking->arrived_at?->format('Y-m-d H:i:s'),
                ]);
            }

            return response([
                "response" => 200,
                "tracking_status" => "not_started",
                "message" => "Tracking not available for this appointment"
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching tracking info: ' . $e->getMessage());
            return Helpers::errorResponse("Error fetching tracking info");
        }
    }

    // Get tracking history for doctor
    public function getDoctorTrackingHistory(Request $request)
    {
        try {
            $trackings = DoctorTrackingModel::with(['appointment'])
                ->where('doctor_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            return response([
                "response" => 200,
                "trackings" => $trackings
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching tracking history: ' . $e->getMessage());
            return Helpers::errorResponse("Error fetching tracking history");
        }
    }
}