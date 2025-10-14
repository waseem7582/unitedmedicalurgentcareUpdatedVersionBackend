<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpecializationSeeder extends Seeder
{
    public function run(): void
    {
        $specializations = [
            ['title' => 'Cardiologist'],
            ['title' => 'Neurologist'],
            ['title' => 'Orthopedic Surgeon'],
            ['title' => 'Pediatrician'],
            ['title' => 'Dermatologist'],
            ['title' => 'Gynecologist'],
            ['title' => 'Psychiatrist'],
            ['title' => 'Radiologist'],
            ['title' => 'Ophthalmologist'],
            ['title' => 'Dentist'],
            ['title' => 'ENT Specialist'],
            ['title' => 'Urologist'],
            ['title' => 'Gastroenterologist'],
            ['title' => 'Nephrologist'],
            ['title' => 'Endocrinologist'],
            ['title' => 'Pulmonologist'],
            ['title' => 'Oncologist'],
            ['title' => 'Rheumatologist'],
            ['title' => 'Anesthesiologist'],
        ];

        foreach ($specializations as &$spec) {
            $spec['created_at'] = now();
            $spec['updated_at'] = now();
        }

        DB::table('specialization')->insert($specializations);
    }
}
