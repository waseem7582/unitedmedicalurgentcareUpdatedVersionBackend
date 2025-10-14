<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OpdTimeSlotSeeder extends Seeder
{
    public function run()
    {
        // Clear all existing time slots
        DB::table('time_slots')->truncate();
        
        // Get all user IDs from users table
        $userIds = DB::table('users')->pluck('id')->toArray();
        
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        
        foreach ($userIds as $userId) {
            foreach ($days as $day) {
                // Set different hours for Saturday vs weekdays
                if ($day === 'Saturday') {
                    $startTime = '09:00:00';
                    $endTime = '14:00:00';
                } else {
                    $startTime = '08:00:00';
                    $endTime = '17:00:00';
                }
                
                // Create single time slot entry using user_id as doct_id
                DB::table('time_slots')->insert([
                    'doct_id'       => $userId, // Using user_id directly
                    'time_start'    => $startTime,
                    'time_end'      => $endTime,
                    'time_duration' => 15,
                    'day'           => $day,
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ]);
            }
        }
    }
}
