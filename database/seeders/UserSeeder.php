<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run()
    {
        // ---- First 5 Users: Admins (User IDs: 1-5) ----
        for ($i = 1; $i <= 5; $i++) {
            $admin = User::create([
                'id' => $i, // Explicitly set ID
                'f_name' => "Admin",
                'l_name' => "$i",
                'phone' => "923008880" . $i,
                'gender' => 'Male',
                'dob' => '1985-01-01',
                'email' => "admin$i@example.com",
                'password' => Hash::make('admin@123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Assign Admin role in role_assign table
            DB::table('users_role_assign')->insert([
                'user_id' => $admin->id,
                'role_id' => 14,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ---- Next 5 Users: Front Desk (User IDs: 6-10) ----
        for ($i = 6; $i <= 10; $i++) {
            $frontDesk = User::create([
                'id' => $i, // Explicitly set ID
                'f_name' => "Front",
                'l_name' => "Desk" . ($i - 5),
                'phone' => "923009990" . ($i - 5),
                'gender' => 'Female',
                'dob' => '1990-01-01',
                'email' => "frontdesk" . ($i - 5) . "@example.com",
                'password' => Hash::make('desk@123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Assign Front Desk role in role_assign table
            DB::table('users_role_assign')->insert([
                'user_id' => $frontDesk->id,
                'role_id' => 16,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ---- Next 20 Users: Doctors (User IDs: 11-30) ----
        for ($i = 11; $i <= 30; $i++) {
            $doctorNumber = $i - 10; // Doctor 1 to 20
            $doctor = User::create([
                'id' => $i, // Explicitly set ID
                'f_name' => "Doctor",
                'l_name' => "$doctorNumber",
                'phone' => "9230011122" . str_pad($doctorNumber, 2, '0', STR_PAD_LEFT),
                'gender' => 'Male',
                'dob' => '1980-01-01',
                'email' => "doctor$doctorNumber@example.com",
                'password' => Hash::make('doctor@123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Assign Doctor role in role_assign table
            DB::table('users_role_assign')->insert([
                'user_id' => $doctor->id,
                'role_id' => 18,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // ---- Last 20 Users: Patients (User IDs: 31-50) ----
        for ($i = 31; $i <= 50; $i++) {
            $patientNumber = $i - 30; // Patient 1 to 20
            User::create([
                'id' => $i, // Explicitly set ID
                'f_name' => "Patient",
                'l_name' => "$patientNumber",
                'phone' => "140855500" . str_pad($patientNumber, 2, '0', STR_PAD_LEFT),
                'gender' => 'Male',
                'dob' => '1995-01-01',
                'email' => "patient$patientNumber@example.com",
                'password' => Hash::make('patient@123'),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            // No role assignment for patients
        }

        $this->command->info('Users created successfully!');
        $this->command->info('Admins: Users 1-5 (password: admin@123)');
        $this->command->info('Front Desk: Users 6-10 (password: desk@123)');
        $this->command->info('Doctors: Users 11-30 (password: doctor@123)');
        $this->command->info('Patients: Users 31-50 (password: patient@123)');
    }
}