<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $department = [
            ['title' => 'General Medicine', 'description' => 'Provides diagnosis and treatment for common illnesses, infections, and chronic conditions such as diabetes and hypertension. Acts as the first point of contact for most health concerns.', 'image' => null, 'active' => 1, 'created_at' => '2025-03-09 09:51:00', 'updated_at' => '2025-08-12 08:57:00'],
            ['title' => 'Pediatrics', 'description' => 'Specializes in the healthcare of infants, children, and adolescents. Includes routine check-ups, vaccinations, and treatment of childhood diseases.', 'image' => null, 'active' => 1, 'created_at' => '2025-03-09 09:51:00', 'updated_at' => '2025-03-09 10:17:00'],
            ['title' => 'Gynecology', 'description' => 'Focuses on women\'s reproductive health, pregnancy care, childbirth, and postnatal care. Also treats conditions such as PCOS, menstrual disorders, and menopause-related issues.', 'image' => null, 'active' => 1, 'created_at' => '2025-03-09 09:51:00', 'updated_at' => '2025-03-09 10:17:00'],
            ['title' => 'Orthopedics', 'description' => 'Deals with the diagnosis and treatment of bone, joint, and muscle disorders. Services include fracture management, arthritis care, and sports injury treatment.', 'image' => null, 'active' => 1, 'created_at' => '2025-03-09 09:52:00', 'updated_at' => '2025-03-09 10:17:00'],
            ['title' => 'Dermatology', 'description' => 'Specializes in skin, hair, and nail disorders, including acne, eczema, psoriasis, and cosmetic skin treatments. Also offers laser therapy and skin rejuvenation.', 'image' => null, 'active' => 1, 'created_at' => '2025-03-09 09:52:00', 'updated_at' => '2025-07-22 08:57:00'],
            ['title' => 'ENT', 'description' => 'Treats conditions related to the ear, nose, throat, and sinuses, including hearing loss, sinusitis, tonsillitis, and voice disorders. Offers treatments like audiometry and endoscopy.', 'image' => null, 'active' => 1, 'created_at' => '2025-03-09 09:52:00', 'updated_at' => '2025-09-19 02:13:00'],
            ['title' => 'Ophthalmology', 'description' => 'Specializes in vision care, cataracts, glaucoma, and refractive errors. Provides eye surgeries and routine check-ups.', 'image' => null, 'active' => 1, 'created_at' => '2025-03-09 10:27:00', 'updated_at' => '2025-03-09 10:32:00'],
            ['title' => 'Neurology', 'description' => 'Deals with disorders of the nervous system, such as migraines, epilepsy, strokes, and nerve-related issues.', 'image' => null, 'active' => 1, 'created_at' => '2025-03-09 10:28:00', 'updated_at' => '2025-06-15 13:50:00'],
            ['title' => 'Dentistry', 'description' => 'Focuses on oral health, including tooth extractions, root canals, orthodontics, and gum disease treatments.', 'image' => null, 'active' => 1, 'created_at' => '2025-03-09 10:28:00', 'updated_at' => '2025-03-09 10:33:00'],
            ['title' => 'Radiology', 'description' => 'Provides imaging services such as X-rays, CT scans, MRI, and ultrasound for diagnosis and treatment.', 'image' => null, 'active' => 1, 'created_at' => '2025-03-09 10:30:00', 'updated_at' => '2025-03-09 10:35:00'],
            ['title' => 'Psychiatry', 'description' => 'Specializes in the diagnosis and treatment of mental health conditions such as depression, anxiety, bipolar disorder, and schizophrenia.', 'image' => null, 'active' => 1, 'created_at' => '2025-03-09 10:31:00', 'updated_at' => '2025-03-09 10:36:00'],
            ['title' => 'Urology', 'description' => 'Treats urinary tract issues and male reproductive system disorders, including kidney stones, prostate problems, and urinary incontinence.', 'image' => null, 'active' => 1, 'created_at' => '2025-03-09 10:32:00', 'updated_at' => '2025-03-09 10:37:00'],
            ['title' => 'Gastroenterology', 'description' => 'Focuses on digestive system disorders, including liver disease, ulcers, IBS, and colon health.', 'image' => null, 'active' => 1, 'created_at' => '2025-03-09 10:33:00', 'updated_at' => '2025-03-09 10:38:00'],
            ['title' => 'Nephrology', 'description' => 'Specializes in kidney health, including dialysis, chronic kidney disease, and kidney failure management.', 'image' => null, 'active' => 1, 'created_at' => '2025-03-09 10:34:00', 'updated_at' => '2025-03-09 10:39:00'],
            ['title' => 'Endocrinology', 'description' => 'Deals with hormone-related disorders such as diabetes, thyroid diseases, and metabolic disorders.', 'image' => null, 'active' => 1, 'created_at' => '2025-03-09 10:35:00', 'updated_at' => '2025-03-09 10:40:00'],
            ['title' => 'Pulmonology', 'description' => 'Treats lung and respiratory disorders, including asthma, COPD, tuberculosis, and pneumonia.', 'image' => null, 'active' => 1, 'created_at' => '2025-03-09 10:36:00', 'updated_at' => '2025-03-09 10:41:00'],
            ['title' => 'Oncology', 'description' => 'Provides diagnosis, treatment, and management of cancers, including chemotherapy, radiotherapy, and supportive care.', 'image' => null, 'active' => 1, 'created_at' => '2025-03-09 10:37:00', 'updated_at' => '2025-03-09 10:42:00'],
            ['title' => 'Rheumatology', 'description' => 'Focuses on autoimmune and musculoskeletal diseases such as arthritis, lupus, and fibromyalgia.', 'image' => null, 'active' => 1, 'created_at' => '2025-03-09 10:38:00', 'updated_at' => '2025-03-09 10:43:00'],
            ['title' => 'Anesthesiology', 'description' => 'Provides anesthesia and perioperative care for patients undergoing surgery and other medical procedures.', 'image' => null, 'active' => 1, 'created_at' => '2025-03-09 10:39:00', 'updated_at' => '2025-03-09 10:44:00']
        ];

        DB::table('department')->insert($department);
    }
}

