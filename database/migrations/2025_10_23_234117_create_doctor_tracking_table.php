<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDoctorTrackingTable extends Migration
{
    public function up()
    {
        // Add coordinates to appointments table first
        Schema::table('appointments', function (Blueprint $table) {
            $table->decimal('patient_lat', 10, 8)->nullable();
            $table->decimal('patient_lng', 11, 8)->nullable();
        });

        Schema::create('doctor_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('appointments')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('users')->onDelete('cascade');
            $table->decimal('current_lat', 10, 8)->nullable();
            $table->decimal('current_lng', 11, 8)->nullable();
            $table->decimal('patient_lat', 10, 8);
            $table->decimal('patient_lng', 11, 8);
            $table->enum('tracking_status', ['not_started', 'traveling', 'arrived', 'completed'])->default('not_started');
            $table->timestamp('tracking_started_at')->nullable();
            $table->timestamp('arrived_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('eta_minutes')->nullable();
            $table->decimal('distance_km', 8, 2)->nullable();
            $table->timestamps();
            
            $table->index(['appointment_id', 'tracking_status']);
            $table->index(['doctor_id', 'tracking_status']);
        });
    }

    public function down()
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropColumn(['patient_lat', 'patient_lng']);
        });
        Schema::dropIfExists('doctor_tracking');
    }
}