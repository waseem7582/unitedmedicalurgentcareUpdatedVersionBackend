<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PatientSeeder extends Seeder
{
    public function run()
    {
        $patients = [];

        for ($i = 31; $i <= 50; $i++) {
            $patientNumber = $i - 30; // Patient 1 to 20
            
            $patients[] = [
                'user_id'     => $i,
                'f_name'      => "Patient",
                'l_name'      => "$patientNumber",
                'isd_code'    => '+1',
                'phone'       => "140855500" . str_pad($patientNumber, 2, '0', STR_PAD_LEFT),
                'city'        => $patientNumber % 2 == 0 ? 'Los Banos' : 'California',
                'state'       => 'California',
                'address'     => "123 Street No. $patientNumber",
                'email'       => "patient$patientNumber@example.com",
                'gender'      => 'Male',
                'dob'         => '1995-01-01',
                'image'       => null,
                'postal_code' => '93635',
                'notes'       => "Demo patient $patientNumber",
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        DB::table('patients')->insert($patients);
        $this->command->info('Patients created for users 31-50!');
    }
}