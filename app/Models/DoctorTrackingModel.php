<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorTrackingModel extends Model
{
    use HasFactory;
    
    protected $table = 'doctor_tracking';
    
    protected $fillable = [
        'appointment_id',
        'doctor_id',
        'current_lat',
        'current_lng',
        'patient_lat',
        'patient_lng',
        'tracking_status',
        'tracking_started_at',
        'arrived_at',
        'completed_at',
        'eta_minutes',
        'distance_km'
    ];

    protected $casts = [
        'tracking_started_at' => 'datetime',
        'arrived_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationship with appointment
    public function appointment()
    {
        return $this->belongsTo(AppointmentModel::class, 'appointment_id');
    }

    // Relationship with doctor
    public function doctor()
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    // Check if tracking is active
    public function isTrackingActive()
    {
        return in_array($this->tracking_status, ['traveling']);
    }

    // Calculate distance to patient (Haversine formula)
    public function calculateDistance()
    {
        if (!$this->current_lat || !$this->current_lng) {
            return null;
        }

        $earthRadius = 6371; // Earth's radius in kilometers

        $latDiff = deg2rad($this->patient_lat - $this->current_lat);
        $lngDiff = deg2rad($this->patient_lng - $this->current_lng);

        $a = sin($latDiff / 2) * sin($latDiff / 2) +
             cos(deg2rad($this->current_lat)) * cos(deg2rad($this->patient_lat)) *
             sin($lngDiff / 2) * sin($lngDiff / 2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        
        return round($earthRadius * $c, 2);
    }

    // Calculate ETA based on distance and traffic
    public function calculateETA()
    {
        $distance = $this->calculateDistance();
        if (!$distance) return null;

        // Simple ETA: 2 minutes per km + 5 minutes buffer
        // In production, you might use Google Maps API for accurate ETA
        return max(5, ceil($distance * 2) + 5);
    }
}