<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

final class DepartmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = [
            ['name' => 'Human Resources', 'shortname' => 'HR'],
            ['name' => 'Finance, Accounting, Tax', 'shortname' => 'FAT'],
            ['name' => 'General Affairs', 'shortname' => 'GA'],
            ['name' => 'Document Control', 'shortname' => 'DC'],
            ['name' => 'Information Technology', 'shortname' => 'IT'],
            ['name' => 'Quality Control', 'shortname' => 'QC'],
            ['name' => 'Production', 'shortname' => 'PRD'],
            ['name' => 'Warehouse & Logistics', 'shortname' => 'WRH'],
            ['name' => 'Research & Development', 'shortname' => 'RND'],
            ['name' => 'Engineering', 'shortname' => 'ENG'],
            ['name' => 'Sales & Marketing', 'shortname' => 'SLS'],
        ];

        foreach ($departments as $department) {
            Department::firstOrCreate(
                ['name' => $department['name']],
                ['shortname' => $department['shortname']]
            );
        }

        $this->command->info('Departments seeded successfully!');
    }
}

