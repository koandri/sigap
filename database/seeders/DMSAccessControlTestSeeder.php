<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DMSAccessControlTestSeeder extends Seeder
{
    public function run(): void
    {
        // Create test roles
        $hrRole = Role::firstOrCreate(['name' => 'HR']);
        $itRole = Role::firstOrCreate(['name' => 'IT']);
        $documentControlRole = Role::firstOrCreate(['name' => 'Document Control']);

        // Create test users with different roles
        $hrUser = User::firstOrCreate(
            ['email' => 'hr@example.com'],
            [
                'name' => 'HR User',
                'password' => Hash::make('password'),
                'manager_id' => null, // No manager for testing
            ]
        );
        $hrUser->assignRole($hrRole);

        $hrUserWithManager = User::firstOrCreate(
            ['email' => 'hr-manager@example.com'],
            [
                'name' => 'HR User with Manager',
                'password' => Hash::make('password'),
                'manager_id' => $hrUser->id, // Has manager
            ]
        );
        $hrUserWithManager->assignRole($hrRole);

        $itUser = User::firstOrCreate(
            ['email' => 'it@example.com'],
            [
                'name' => 'IT User',
                'password' => Hash::make('password'),
                'manager_id' => null,
            ]
        );
        $itUser->assignRole($itRole);

        $documentControl = User::firstOrCreate(
            ['email' => 'doccontrol@example.com'],
            [
                'name' => 'Document Control User',
                'password' => Hash::make('password'),
                'manager_id' => null,
            ]
        );
        $documentControl->assignRole($documentControlRole);

        // Create a test document in HR department
        $document = Document::firstOrCreate(
            ['document_number' => 'HR-SOP-001'],
            [
                'title' => 'HR SOP Document',
                'description' => 'A test HR SOP document',
                'document_type' => \App\Enums\DocumentType::SOP,
                'department_id' => $hrRole->id,
                'created_by' => $documentControl->id, // Created by Document Control
            ]
        );

        // Create a version for testing
        if (!$document->versions()->exists()) {
            DocumentVersion::create([
                'document_id' => $document->id,
                'version_number' => 0,
                'file_path' => 'test/hr-sop-001-v1.pdf',
                'file_type' => 'pdf',
                'status' => \App\Enums\DocumentVersionStatus::Active,
                'created_by' => $documentControl->id,
                'revision_description' => 'Initial version',
            ]);
        }
    }
}
