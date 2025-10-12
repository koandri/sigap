<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Form;
use App\Models\FormVersion;

class FormVersionController extends Controller
{
    /**
     * Display versions for a form
     */
    public function index(Form $form)
    {
        $versions = $form->versions()
            ->with(['creator', 'fields'])
            ->orderBy('version_number', 'desc')
            ->get();
            
        return view('formversions.index', compact('form', 'versions'));
    }

    /**
     * Show create version form
     */
    public function create(Form $form)
    {
        $nextVersion = $form->getNextVersionNumber();
        $activeVersion = $form->activeVersion;
        
        return view('formversions.create', compact('form', 'nextVersion', 'activeVersion'));
    }

    /**
     * Store new version
     */
    public function store(Request $request, Form $form)
    {
        $validated = $request->validate([
            'description' => 'nullable|string',
            'copy_from_version' => 'nullable|exists:form_versions,id',
            'make_active' => 'boolean'
        ]);
        
        // Create new version
        $version = $form->versions()->create([
            'version_number' => $form->getNextVersionNumber(),
            'description' => $validated['description'],
            'is_active' => false,
            'created_by' => auth()->id(),
            'created_on' => now()
        ]);

        // Copy fields from another version if requested
        if ($request->filled('copy_from_version')) {
            $sourceVersion = FormVersion::find($validated['copy_from_version']);
            if ($sourceVersion && $sourceVersion->form_id == $form->id) {
                $this->copyFields($sourceVersion, $version);
            }
        }

        // Activate if requested
        if ($request->has('make_active')) {
            $version->activate();
        }

        return redirect()->route('formversions.show', [$form, $version])
            ->with('success', 'Version created successfully.');
    }

    /**
     * Show version details
     */
    public function show(Form $form, FormVersion $version)
    {
        // Verify version belongs to form
        if ($version->form_id != $form->id) {
            abort(404);
        }

        // Load fields dengan ordering
    $version->load(['creator']);
    
    // Get ordered fields separately
    $orderedFields = $version->fields()
        ->with('options')
        ->ordered()
        ->get();

    return view('formversions.show', compact('form', 'version', 'orderedFields'));
    }

    /**
     * Activate a version
     */
    public function activate(Form $form, FormVersion $version)
    {
        // Verify version belongs to form
        if ($version->form_id != $form->id) {
            abort(404);
        }

        // Check if version has at least one field
        if ($version->fields->count() == 0) {
            return back()->with('error', 'Cannot activate version without fields.');
        }

        $version->activate();

        return back()->with('success', 'Version activated successfully.');
    }

    /**
     * Delete a version
     */
    public function destroy(Form $form, FormVersion $version)
    {
        // Verify version belongs to form
        if ($version->form_id != $form->id) {
            abort(404);
        }

        // Check if version has submissions
        if ($version->submissions()->exists()) {
            return back()->with('error', 'Cannot delete version with existing submissions.');
        }

        // Check if it's the only version
        if ($form->versions->count() == 1) {
            return back()->with('error', 'Cannot delete the only version.');
        }

        // If active, deactivate first
        if ($version->is_active) {
            return back()->with('error', 'Cannot delete active version. Please activate another version first.');
        }

        $version->delete();

        return redirect()->route('forms.show', $form)
            ->with('success', 'Version deleted successfully.');
    }

    /**
     * Copy fields from one version to another
     */
    private function copyFields($sourceVersion, $targetVersion)
    {
        foreach ($sourceVersion->fields as $field) {
            $newField = $field->replicate();
            $newField->form_version_id = $targetVersion->id;
            $newField->created_at = now();
            $newField->updated_at = now();
            $newField->save();

            // Copy field options if any
            if ($field->options->count() > 0) {
                foreach ($field->options as $option) {
                    $newOption = $option->replicate();
                    $newOption->form_field_id = $newField->id;
                    $newOption->created_at = now();
                    $newOption->updated_at = now();
                    $newOption->save();
                }
            }
        }
    }
}