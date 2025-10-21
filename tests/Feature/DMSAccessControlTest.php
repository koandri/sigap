<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DMSAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_owner_document_control_can_create_documents(): void
    {
        // Create roles
        $hrRole = Role::factory()->create(['name' => 'HR']);
        $documentControlRole = Role::factory()->create(['name' => 'Document Control']);

        // Create users
        $hrUser = User::factory()->create();
        $hrUser->assignRole($hrRole);

        $documentControl = User::factory()->create();
        $documentControl->assignRole($documentControlRole);

        // HR user should NOT be able to create documents
        $response = $this->actingAs($hrUser)
            ->get(route('documents.create'));
        $response->assertForbidden();

        // Document Control should be able to create documents
        $response = $this->actingAs($documentControl)
            ->get(route('documents.create'));
        $response->assertOk();
    }

    public function test_department_users_can_create_versions_for_their_department_documents(): void
    {
        // Create roles
        $hrRole = Role::factory()->create(['name' => 'HR']);
        $itRole = Role::factory()->create(['name' => 'IT']);
        $documentControlRole = Role::factory()->create(['name' => 'Document Control']);

        // Create users
        $hrUser = User::factory()->create(['manager_id' => 1]);
        $hrUser->assignRole($hrRole);

        $itUser = User::factory()->create(['manager_id' => 1]);
        $itUser->assignRole($itRole);

        $documentControl = User::factory()->create();
        $documentControl->assignRole($documentControlRole);

        // Create document in HR department
        $document = Document::factory()->create([
            'department_id' => $hrRole->id,
            'created_by' => $documentControl->id,
        ]);

        // HR user should be able to create versions for HR documents
        $response = $this->actingAs($hrUser)
            ->get(route('documents.versions.create', $document));
        $response->assertOk();

        // IT user should NOT be able to create versions for HR documents
        $response = $this->actingAs($itUser)
            ->get(route('documents.versions.create', $document));
        $response->assertForbidden();
    }

    public function test_only_creator_or_manager_can_edit_versions(): void
    {
        // Create roles
        $hrRole = Role::factory()->create(['name' => 'HR']);
        $documentControlRole = Role::factory()->create(['name' => 'Document Control']);

        // Create users
        $hrUser = User::factory()->create(['manager_id' => 1]);
        $hrUser->assignRole($hrRole);

        $hrManager = User::factory()->create();
        $hrUser->update(['manager_id' => $hrManager->id]);

        $otherHrUser = User::factory()->create(['manager_id' => 1]);
        $otherHrUser->assignRole($hrRole);

        $documentControl = User::factory()->create();
        $documentControl->assignRole($documentControlRole);

        // Create document and version
        $document = Document::factory()->create([
            'department_id' => $hrRole->id,
            'created_by' => $documentControl->id,
        ]);

        $version = DocumentVersion::factory()->create([
            'document_id' => $document->id,
            'created_by' => $hrUser->id,
            'status' => \App\Enums\DocumentVersionStatus::Draft,
        ]);

        // Creator should be able to edit
        $response = $this->actingAs($hrUser)
            ->get(route('document-versions.edit', $version));
        $response->assertOk();

        // Manager should be able to edit
        $response = $this->actingAs($hrManager)
            ->get(route('document-versions.edit', $version));
        $response->assertOk();

        // Other HR user should NOT be able to edit
        $response = $this->actingAs($otherHrUser)
            ->get(route('document-versions.edit', $version));
        $response->assertForbidden();
    }
}
