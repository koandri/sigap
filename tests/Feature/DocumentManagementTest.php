<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class DocumentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_document(): void
    {
        $user = User::factory()->create();
        $department = Role::factory()->create(['name' => 'Test Department']);
        
        $user->assignRole($department);

        $response = $this->actingAs($user)
            ->post(route('documents.store'), [
                'title' => 'Test Document',
                'description' => 'Test Description',
                'document_type' => 'SOP',
                'department_id' => $department->id,
                'document_number' => 'SOP-001',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('documents', [
            'title' => 'Test Document',
            'document_type' => 'SOP',
            'department_id' => $department->id,
        ]);
    }

    public function test_user_can_view_document(): void
    {
        $user = User::factory()->create();
        $department = Role::factory()->create(['name' => 'Test Department']);
        
        $user->assignRole($department);

        $document = Document::factory()->create([
            'department_id' => $department->id,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)
            ->get(route('documents.show', $document));

        $response->assertOk();
        $response->assertSee($document->title);
    }

    public function test_user_can_create_document_version(): void
    {
        $user = User::factory()->create();
        $department = Role::factory()->create(['name' => 'Test Department']);
        
        $user->assignRole($department);

        $document = Document::factory()->create([
            'department_id' => $department->id,
            'created_by' => $user->id,
        ]);

        $file = \Illuminate\Http\UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)
            ->post(route('documents.versions.store', $document), [
                'creation_method' => 'upload',
                'source_file' => $file,
                'revision_description' => 'Initial version',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('document_versions', [
            'document_id' => $document->id,
            'version_number' => 0,
        ]);
    }

    public function test_user_can_request_document_access(): void
    {
        $user = User::factory()->create();
        $department = Role::factory()->create(['name' => 'Test Department']);
        
        $user->assignRole($department);

        $document = Document::factory()->create([
            'document_type' => 'SOP',
            'department_id' => $department->id,
        ]);

        $version = DocumentVersion::factory()->create([
            'document_id' => $document->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)
            ->post(route('documents.request-access', $document), [
                'access_type' => 'one_time',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('document_access_requests', [
            'document_version_id' => $version->id,
            'user_id' => $user->id,
            'access_type' => 'one_time',
        ]);
    }
}
