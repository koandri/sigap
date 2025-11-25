<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\ComponentType;
use App\Models\Asset;
use App\Models\AssetComponent;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class AssetComponentService
{
    /**
     * Attach a component to a parent asset.
     */
    public function attachComponent(
        Asset $parent,
        Asset $component,
        ComponentType $componentType,
        ?\DateTime $installedDate = null,
        ?float $installedUsageValue = null,
        ?string $notes = null
    ): AssetComponent {
        try {
            DB::beginTransaction();

            // Validate attachment
            $validation = $this->validateComponentAttachment($parent, $component);
            if (!$validation['valid']) {
                throw new \InvalidArgumentException($validation['message']);
            }

            // Create component relationship
            $assetComponent = AssetComponent::create([
                'parent_asset_id' => $parent->id,
                'component_asset_id' => $component->id,
                'component_type' => $componentType,
                'installed_date' => $installedDate?->format('Y-m-d') ?? now()->format('Y-m-d'),
                'installed_usage_value' => $installedUsageValue,
                'installation_notes' => $notes,
            ]);

            DB::commit();

            return $assetComponent->fresh(['parentAsset', 'componentAsset']);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to attach component: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Detach a component from its parent asset.
     */
    public function detachComponent(
        AssetComponent $componentRelationship,
        ?string $reason = null,
        ?float $disposedUsageValue = null
    ): AssetComponent {
        try {
            DB::beginTransaction();

            $componentRelationship->update([
                'removed_date' => now(),
                'removed_by' => auth()->id(),
                'removal_reason' => $reason,
                'disposed_usage_value' => $disposedUsageValue,
            ]);

            DB::commit();

            return $componentRelationship->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to detach component: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Replace a component with a new one.
     */
    public function replaceComponent(
        AssetComponent $oldComponent,
        Asset $newComponentAsset,
        ?string $removalReason = null,
        ?float $disposedUsageValue = null,
        ?float $newInstalledUsageValue = null,
        ?string $installationNotes = null
    ): AssetComponent {
        try {
            DB::beginTransaction();

            // Remove old component
            $this->detachComponent($oldComponent, $removalReason, $disposedUsageValue);

            // Attach new component
            $newComponent = $this->attachComponent(
                $oldComponent->parentAsset,
                $newComponentAsset,
                $oldComponent->component_type,
                now(),
                $newInstalledUsageValue,
                $installationNotes
            );

            DB::commit();

            return $newComponent;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to replace component: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get active components for an asset.
     */
    public function getActiveComponents(Asset $asset, ?ComponentType $componentType = null): Collection
    {
        $query = $asset->activeComponents();

        if ($componentType !== null) {
            $query->where('component_type', $componentType);
        }

        return $query->get();
    }

    /**
     * Get all components (including removed) for an asset.
     */
    public function getAllComponents(Asset $asset, ?ComponentType $componentType = null): Collection
    {
        $query = $asset->componentRelationships();

        if ($componentType !== null) {
            $query->where('component_type', $componentType);
        }

        return $query->orderBy('installed_date', 'desc')->get();
    }

    /**
     * Get component history for an asset.
     */
    public function getComponentHistory(Asset $asset): Collection
    {
        return $asset->componentRelationships()
            ->with(['componentAsset', 'removedBy'])
            ->orderBy('installed_date', 'desc')
            ->get();
    }

    /**
     * Get parent assets for a component.
     */
    public function getParentAssets(Asset $component, bool $activeOnly = true): Collection
    {
        $query = $activeOnly 
            ? $component->activeParentRelationships()
            : $component->parentRelationships();

        return $query->with('parentAsset')->get()->pluck('parentAsset');
    }

    /**
     * Validate component attachment.
     */
    public function validateComponentAttachment(Asset $parent, Asset $component): array
    {
        // Check if parent and component are different
        if ($parent->id === $component->id) {
            return [
                'valid' => false,
                'message' => 'An asset cannot be attached to itself.',
            ];
        }

        // Check for circular references
        if ($this->wouldCreateCircularReference($parent, $component)) {
            return [
                'valid' => false,
                'message' => 'This attachment would create a circular reference.',
            ];
        }

        // Check if component is already attached to this parent
        $existingActive = AssetComponent::where('parent_asset_id', $parent->id)
            ->where('component_asset_id', $component->id)
            ->active()
            ->exists();

        if ($existingActive) {
            return [
                'valid' => false,
                'message' => 'This component is already attached to this asset.',
            ];
        }

        return [
            'valid' => true,
            'message' => 'Attachment is valid.',
        ];
    }

    /**
     * Check if attaching would create a circular reference.
     */
    private function wouldCreateCircularReference(Asset $parent, Asset $component): bool
    {
        // If component has the parent as one of its components, it would create a cycle
        $componentAssets = $component->getComponentAssets();
        
        if ($componentAssets->contains('id', $parent->id)) {
            return true;
        }

        // Recursively check nested components
        foreach ($componentAssets as $childAsset) {
            if ($this->wouldCreateCircularReference($parent, $childAsset)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get component tree for an asset.
     */
    public function getComponentTree(Asset $asset, int $depth = 0): array
    {
        $tree = [
            'asset' => $asset,
            'depth' => $depth,
            'children' => [],
        ];

        $components = $asset->activeComponents()->with('componentAsset')->get();
        
        foreach ($components as $componentRelationship) {
            $tree['children'][] = $this->getComponentTree(
                $componentRelationship->componentAsset,
                $depth + 1
            );
        }

        return $tree;
    }

    /**
     * Get statistics for an asset's components.
     */
    public function getComponentStatistics(Asset $asset): array
    {
        $allComponents = $asset->componentRelationships;
        $activeComponents = $asset->activeComponents;

        return [
            'total' => $allComponents->count(),
            'active' => $activeComponents->count(),
            'removed' => $allComponents->count() - $activeComponents->count(),
            'by_type' => [
                'consumable' => $activeComponents->where('component_type', ComponentType::Consumable)->count(),
                'replaceable' => $activeComponents->where('component_type', ComponentType::Replaceable)->count(),
                'integral' => $activeComponents->where('component_type', ComponentType::Integral)->count(),
            ],
        ];
    }
}
