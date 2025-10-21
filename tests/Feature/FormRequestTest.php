<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\FormRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FormRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_form_request(): void
    {
        $user = User::factory()->create();
        $department = Role::factory()->create(['name' => 'Test Department']);
        
        $user->assignRole($department);

        $document = Document::factory()->create([
            'document_type' => 'Form',
            'department_id' => $department->id,
        ]);

        $version = DocumentVersion::factory()->create([
            'document_id' => $document->id,
            'status' => 'approved',
        ]);

        $response = $this->actingAs($user)
            ->post(route('form-requests.store'), [
                'items' => [
                    [
                        'document_version_id' => $version->id,
                        'quantity' => 5,
                    ],
                ],
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('form_requests', [
            'requested_by' => $user->id,
            'status' => 'requested',
        ]);
    }

    public function test_document_control_can_acknowledge_form_request(): void
    {
        $user = User::factory()->create();
        $department = Role::factory()->create(['name' => 'Test Department']);
        $documentControl = Role::factory()->create(['name' => 'Document Control']);
        
        $user->assignRole($department);
        $documentControlUser = User::factory()->create();
        $documentControlUser->assignRole($documentControl);

        $document = Document::factory()->create([
            'document_type' => 'Form',
            'department_id' => $department->id,
        ]);

        $version = DocumentVersion::factory()->create([
            'document_id' => $document->id,
            'status' => 'approved',
        ]);

        $formRequest = FormRequest::factory()->create([
            'requested_by' => $user->id,
            'status' => 'requested',
        ]);

        $response = $this->actingAs($documentControlUser)
            ->post(route('form-requests.acknowledge', $formRequest));

        $response->assertRedirect();
        $this->assertDatabaseHas('form_requests', [
            'id' => $formRequest->id,
            'status' => 'acknowledged',
        ]);
    }

    public function test_document_control_can_mark_form_request_ready(): void
    {
        $user = User::factory()->create();
        $department = Role::factory()->create(['name' => 'Test Department']);
        $documentControl = Role::factory()->create(['name' => 'Document Control']);
        
        $user->assignRole($department);
        $documentControlUser = User::factory()->create();
        $documentControlUser->assignRole($documentControl);

        $document = Document::factory()->create([
            'document_type' => 'Form',
            'department_id' => $department->id,
        ]);

        $version = DocumentVersion::factory()->create([
            'document_id' => $document->id,
            'status' => 'approved',
        ]);

        $formRequest = FormRequest::factory()->create([
            'requested_by' => $user->id,
            'status' => 'processing',
        ]);

        $response = $this->actingAs($documentControlUser)
            ->post(route('form-requests.ready', $formRequest));

        $response->assertRedirect();
        $this->assertDatabaseHas('form_requests', [
            'id' => $formRequest->id,
            'status' => 'ready_for_collection',
        ]);
    }
}
