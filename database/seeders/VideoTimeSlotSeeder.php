<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VideoTimeSlotSeeder extends Seeder
{
    public function run()
    {
        // Clear all existing video time slots
        DB::table('video_time_slots')->truncate();
        
        // Get all user IDs from users table (same as time_slots)
        $userIds = DB::table('users')->pluck('id')->toArray();
        
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        
        foreach ($userIds as $userId) {
            foreach ($days as $day) {
                // Set video consultation hours (different from regular slots)
                if ($day === 'Saturday') {
                    $startTime = '10:00:00';
                    $endTime = '15:00:00';
                } else {
                    $startTime = '18:00:00';  // Evening hours for video consults
                    $endTime = '21:00:00';
                }
                
                // Create single time slot entry using user_id as doct_id
                // This matches the format your system expects
                DB::table('video_time_slots')->insert([
                    'doct_id'       => $userId, // Using user_id directly
                    'time_start'    => $startTime,
                    'time_end'      => $endTime,
                    'time_duration' => 20, // 20 minutes for video appointments
                    'day'           => $day,
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ]);
            }
        }
    }
}