<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\PrintedForm;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class DocumentManagementLocationReportController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('dms.reports.view');
        
        // Get filters
        $filters = [
            'type' => $request->input('type', 'all'), // all, documents, forms
            'room_no' => $request->input('room_no'),
            'cabinet_no' => $request->input('cabinet_no'),
            'shelf_no' => $request->input('shelf_no'),
            'search' => $request->input('search'), // For finding locations of specific documents/forms
        ];
        
        $documents = collect();
        $printedForms = collect();
        
        // Search by location (multiple locations can be selected)
        if ($filters['type'] === 'all' || $filters['type'] === 'documents') {
            $docQuery = Document::whereNotNull('physical_location');
            
            if ($filters['room_no']) {
                $docQuery->whereJsonContains('physical_location->room_no', $filters['room_no']);
            }
            if ($filters['cabinet_no']) {
                $docQuery->whereJsonContains('physical_location->cabinet_no', $filters['cabinet_no']);
            }
            if ($filters['shelf_no']) {
                $docQuery->whereJsonContains('physical_location->shelf_no', $filters['shelf_no']);
            }
            
            $documents = $docQuery->with('department')->get();
        }
        
        if ($filters['type'] === 'all' || $filters['type'] === 'forms') {
            $formQuery = PrintedForm::whereNotNull('physical_location')
                ->where('status', 'scanned');
            
            if ($filters['room_no']) {
                $formQuery->whereJsonContains('physical_location->room_no', $filters['room_no']);
            }
            if ($filters['cabinet_no']) {
                $formQuery->whereJsonContains('physical_location->cabinet_no', $filters['cabinet_no']);
            }
            if ($filters['shelf_no']) {
                $formQuery->whereJsonContains('physical_location->shelf_no', $filters['shelf_no']);
            }
            
            $printedForms = $formQuery->with([
                'documentVersion.document',
                'issuedTo',
                'formRequestItem.formRequest.requester'
            ])->get();
        }
        
        // Find locations of specific documents/forms
        $searchResults = collect();
        if ($filters['search']) {
            // Search in documents
            $docQuery = Document::where(function($q) use ($filters) {
                $q->where('document_number', 'like', "%{$filters['search']}%")
                  ->orWhere('title', 'like', "%{$filters['search']}%");
            })->whereNotNull('physical_location');
            
            $docs = $docQuery->with('department')->get();
            
            foreach ($docs as $doc) {
                $searchResults->push([
                    'type' => 'document',
                    'item' => $doc,
                    'location' => $doc->physical_location,
                    'location_string' => $doc->physical_location_string,
                ]);
            }
            
            // Search in printed forms
            $formQuery = PrintedForm::where(function($q) use ($filters) {
                $q->where('form_number', 'like', "%{$filters['search']}%")
                  ->orWhereHas('documentVersion.document', function($subQ) use ($filters) {
                      $subQ->where('document_number', 'like', "%{$filters['search']}%")
                           ->orWhere('title', 'like', "%{$filters['search']}%");
                  });
            })->whereNotNull('physical_location')
              ->where('status', 'scanned');
            
            $forms = $formQuery->with([
                'documentVersion.document',
                'issuedTo',
                'formRequestItem.formRequest.requester'
            ])->get();
            
            foreach ($forms as $form) {
                $searchResults->push([
                    'type' => 'printed_form',
                    'item' => $form,
                    'location' => $form->physical_location,
                    'location_string' => $form->physical_location_string,
                ]);
            }
        }
        
        return view('location-reports.index', compact('documents', 'printedForms', 'filters', 'searchResults'));
    }

    public function groupByLocation(Request $request): View
    {
        $this->authorize('dms.reports.view');
        
        $filters = [
            'type' => $request->input('type', 'all'), // all, documents, forms
        ];
        
        // Group documents by location
        $documentsByLocation = collect();
        if ($filters['type'] === 'all' || $filters['type'] === 'documents') {
            $docQuery = Document::whereNotNull('physical_location');
            
            $docs = $docQuery->with('department')->get();
            
            $documentsByLocation = $docs->groupBy(function ($doc) {
                $loc = $doc->physical_location;
                return sprintf(
                    '%s|%s|%s',
                    $loc['room_no'] ?? 'N/A',
                    $loc['cabinet_no'] ?? 'N/A',
                    $loc['shelf_no'] ?? 'N/A'
                );
            })->map(function ($items, $key) {
                [$room, $cabinet, $shelf] = explode('|', $key);
                return [
                    'room_no' => $room,
                    'cabinet_no' => $cabinet,
                    'shelf_no' => $shelf,
                    'items' => $items,
                    'count' => $items->count(),
                ];
            })->sortBy(['room_no', 'cabinet_no', 'shelf_no']);
        }
        
        // Group printed forms by location
        $formsByLocation = collect();
        if ($filters['type'] === 'all' || $filters['type'] === 'forms') {
            $formQuery = PrintedForm::whereNotNull('physical_location')
                ->where('status', 'scanned');
            
            $forms = $formQuery->with([
                'documentVersion.document',
                'issuedTo',
                'formRequestItem.formRequest.requester'
            ])->get();
            
            $formsByLocation = $forms->groupBy(function ($form) {
                $loc = $form->physical_location;
                return sprintf(
                    '%s|%s|%s',
                    $loc['room_no'] ?? 'N/A',
                    $loc['cabinet_no'] ?? 'N/A',
                    $loc['shelf_no'] ?? 'N/A'
                );
            })->map(function ($items, $key) {
                [$room, $cabinet, $shelf] = explode('|', $key);
                return [
                    'room_no' => $room,
                    'cabinet_no' => $cabinet,
                    'shelf_no' => $shelf,
                    'items' => $items,
                    'count' => $items->count(),
                ];
            })->sortBy(['room_no', 'cabinet_no', 'shelf_no']);
        }
        
        return view('location-reports.group-by-location', compact('documentsByLocation', 'formsByLocation', 'filters'));
    }
}

