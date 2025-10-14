<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseTruncator extends Seeder
{
    public function run()
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        // Truncate tables in correct order to maintain foreign key constraints
        DB::table('doctors')->truncate();
        DB::table('patients')->truncate();
        DB::table('users_role_assign')->truncate();
        DB::table('users')->truncate();
        
        // Enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->command->info('All tables truncated successfully!');
    }
}