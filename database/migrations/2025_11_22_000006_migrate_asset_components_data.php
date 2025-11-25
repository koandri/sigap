<?php

declare(strict_types=1);

use App\Models\AssetComponent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Only migrate if the parent_asset_id column exists in the assets table
        if (!Schema::hasColumn('assets', 'parent_asset_id')) {
            return;
        }

        // Migrate existing component relationships from assets table to asset_components table
        DB::table('assets')
            ->whereNotNull('parent_asset_id')
            ->orderBy('id')
            ->chunk(100, function ($assets) {
                foreach ($assets as $asset) {
                    AssetComponent::create([
                        'parent_asset_id' => $asset->parent_asset_id,
                        'component_asset_id' => $asset->id,
                        'component_type' => $asset->component_type ?? 'replaceable',
                        'installed_date' => $asset->installed_date ?? $asset->created_at,
                        'installed_usage_value' => $asset->installed_usage_value,
                        'disposed_usage_value' => $asset->disposed_usage_value,
                        'installation_notes' => $asset->installation_notes,
                        'created_at' => $asset->created_at,
                        'updated_at' => $asset->updated_at,
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Delete all migrated component relationships
        AssetComponent::truncate();
    }
};
