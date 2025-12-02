<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

final class LocationController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:options.locations.view')->only(['index', 'show']);
        $this->middleware('can:options.locations.create')->only(['create', 'store']);
        $this->middleware('can:options.locations.edit')->only(['edit', 'update']);
        $this->middleware('can:options.locations.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $query = Location::withCount('assets');

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // Search by name or code
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $locations = $query->orderBy('name')->paginate(20);

        return view('options.locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('options.locations.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:locations,code',
            'is_active' => 'boolean',
        ]);

        Location::create($validated);

        return redirect()
            ->route('options.locations.index')
            ->with('success', 'Location created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Location $location): View
    {
        $location->load(['assets.assetCategory', 'assets.department', 'assets.user']);
        
        return view('options.locations.show', compact('location'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Location $location): View
    {
        return view('options.locations.edit', compact('location'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Location $location): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50|unique:locations,code,' . $location->id,
            'is_active' => 'boolean',
        ]);

        $location->update($validated);

        return redirect()
            ->route('options.locations.index')
            ->with('success', 'Location updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Location $location): RedirectResponse
    {
        if ($location->assets()->count() > 0) {
            return redirect()
                ->route('options.locations.index')
                ->with('error', 'Cannot delete location with existing assets.');
        }

        $location->delete();

        return redirect()
            ->route('options.locations.index')
            ->with('success', 'Location deleted successfully.');
    }
}

