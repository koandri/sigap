<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\FormRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

final class DMSTestDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create test roles
        $hrRole = Role::firstOrCreate(['name' => 'HR']);
        $itRole = Role::firstOrCreate(['name' => 'IT']);
        $documentControlRole = Role::firstOrCreate(['name' => 'Document Control']);

        // Create test users
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
            ]
        );
        $user->assignRole($hrRole);

        $documentControl = User::firstOrCreate(
            ['email' => 'doccontrol@example.com'],
            [
                'name' => 'Document Control User',
                'password' => Hash::make('password'),
            ]
        );
        $documentControl->assignRole($documentControlRole);

        // Create test documents
        $document = Document::firstOrCreate(
            ['document_number' => 'SOP-001'],
            [
                'title' => 'Test SOP Document',
                'description' => 'A test SOP document',
                'document_type' => \App\Enums\DocumentType::SOP,
                'department_id' => $hrRole->id,
                'created_by' => $user->id,
            ]
        );

        // Create document version
        $version = DocumentVersion::create([
            'document_id' => $document->id,
            'version_number' => 0,
            'file_path' => 'test/sop-001-v1.pdf',
            'file_type' => 'pdf',
            'status' => \App\Enums\DocumentVersionStatus::Active,
            'created_by' => $user->id,
            'revision_description' => 'Initial version',
        ]);

        // Create test form request
        FormRequest::create([
            'requested_by' => $user->id,
            'request_date' => now()->subHours(3), // 3 hours ago (overdue)
            'status' => 'requested',
        ]);
    }
}
