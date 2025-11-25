# Asset Model Refactoring - Implementation Plan

Comprehensive refactoring of the Asset model to improve separation of concerns, type safety, extensibility, and maintainability based on architectural review.

## User Review Required

> [!IMPORTANT]
> **Breaking Changes**: This refactoring will introduce database schema changes and API modifications. Existing code that directly accesses `parent_asset_id`, `is_active`, or JSON fields will need updates.

> [!WARNING]
> **Timeline**: This is a multi-phase refactor estimated at 3-4 weeks. Consider business priorities and whether all phases are needed immediately.

> [!CAUTION]
> **Data Migration**: Phase 4 (Component Model) and Phase 8 (Soft Deletes) require careful data migration. Ensure database backups are in place.

**Decision Points:**
1. Should we implement all phases or prioritize specific improvements?
2. Can we afford 3-4 weeks for this refactor, or should we break it into smaller releases?
3. Do we have adequate test coverage to safely refactor?

---

## Proposed Changes

### Phase 1: Foundation & Preparation

Establish safety nets before making changes.

#### [NEW] [AssetTest.php](file:///Users/andri/Documents/projects/sigap/tests/Unit/Models/AssetTest.php)

Comprehensive unit tests for Asset model covering:
- Relationship loading
- Scope queries (active, disposed, components)
- Lifetime calculations
- Helper methods (isComponent, hasComponents, primaryPhoto)
- Edge cases (null values, circular references)

#### [NEW] [AssetIntegrationTest.php](file:///Users/andri/Documents/projects/sigap/tests/Feature/AssetIntegrationTest.php)

Integration tests covering:
- Asset creation workflow
- Component attachment/detachment
- Disposal process
- Photo management
- QR code generation

#### [NEW] [ASSET_BEHAVIOR.md](file:///Users/andri/Documents/projects/sigap/docs/ASSET_BEHAVIOR.md)

Documentation of current behavior including:
- Status transition rules
- Component hierarchy constraints
- Lifetime calculation formulas
- Photo/document management rules

---

### Phase 2: Value Objects & Type Safety

Replace loose arrays with structured value objects for better type safety and validation.

#### [NEW] [AssetSpecifications.php](file:///Users/andri/Documents/projects/sigap/app/ValueObjects/AssetSpecifications.php)

```php
<?php

namespace App\ValueObjects;

use Illuminate\Contracts\Database\Eloquent\Castable;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

final class AssetSpecifications implements Castable
{
    public function __construct(
        public readonly ?string $voltage = null,
        public readonly ?string $power = null,
        public readonly ?string $weight = null,
        public readonly ?array $dimensions = null,
        public readonly array $custom = []
    ) {}

    public static function fromArray(?array $data): ?self
    {
        if ($data === null) {
            return null;
        }

        return new self(
            voltage: $data['voltage'] ?? $data['Voltage'] ?? null,
            power: $data['power'] ?? $data['Power'] ?? null,
            weight: $data['weight'] ?? $data['Weight'] ?? null,
            dimensions: $data['dimensions'] ?? $data['Dimensions'] ?? null,
            custom: array_diff_key($data, array_flip(['voltage', 'power', 'weight', 'dimensions']))
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'voltage' => $this->voltage,
            'power' => $this->power,
            'weight' => $this->weight,
            'dimensions' => $this->dimensions,
        ] + $this->custom, fn($value) => $value !== null);
    }

    public function hasElectricalSpecs(): bool
    {
        return $this->voltage !== null || $this->power !== null;
    }

    public function getFormattedWeight(): ?string
    {
        return $this->weight ? "{$this->weight} kg" : null;
    }

    public static function castUsing(array $arguments): CastsAttributes
    {
        return new class implements CastsAttributes {
            public function get($model, $key, $value, $attributes)
            {
                return AssetSpecifications::fromArray(json_decode($value, true));
            }

            public function set($model, $key, $value, $attributes)
            {
                if ($value instanceof AssetSpecifications) {
                    return json_encode($value->toArray());
                }
                
                if (is_array($value)) {
                    return json_encode(AssetSpecifications::fromArray($value)?->toArray());
                }

                return $value;
            }
        };
    }
}
```

#### [NEW] [GpsData.php](file:///Users/andri/Documents/projects/sigap/app/ValueObjects/GpsData.php)

```php
<?php

namespace App\ValueObjects;

final class GpsData
{
    public function __construct(
        public readonly float $latitude,
        public readonly float $longitude,
        public readonly ?float $altitude = null,
        public readonly ?float $accuracy = null
    ) {}

    public static function fromArray(?array $data): ?self
    {
        if ($data === null || !isset($data['latitude'], $data['longitude'])) {
            return null;
        }

        return new self(
            latitude: (float) $data['latitude'],
            longitude: (float) $data['longitude'],
            altitude: isset($data['altitude']) ? (float) $data['altitude'] : null,
            accuracy: isset($data['accuracy']) ? (float) $data['accuracy'] : null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'altitude' => $this->altitude,
            'accuracy' => $this->accuracy,
        ], fn($value) => $value !== null);
    }

    public function getGoogleMapsUrl(): string
    {
        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    public function distanceTo(GpsData $other): float
    {
        // Haversine formula for distance calculation
        $earthRadius = 6371; // km

        $latDelta = deg2rad($other->latitude - $this->latitude);
        $lonDelta = deg2rad($other->longitude - $this->longitude);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
             cos(deg2rad($this->latitude)) * cos(deg2rad($other->latitude)) *
             sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}
```

#### [MODIFY] [Asset.php](file:///Users/andri/Documents/projects/sigap/app/Models/Asset.php)

Update casts to use value objects:
```php
protected $casts = [
    'purchase_date' => 'date',
    'warranty_expiry' => 'date',
    'disposed_date' => 'date',
    'installed_date' => 'date',
    'specifications' => AssetSpecifications::class, // Changed
    'is_active' => 'boolean',
    'installed_usage_value' => 'decimal:2',
    'disposed_usage_value' => 'decimal:2',
    'expected_lifetime_value' => 'decimal:2',
    'actual_lifetime_value' => 'decimal:2',
    'component_type' => ComponentType::class,
    'lifetime_unit' => UsageUnit::class,
];
```

#### [MODIFY] [AssetPhoto.php](file:///Users/andri/Documents/projects/sigap/app/Models/AssetPhoto.php)

Update casts:
```php
protected $casts = [
    'uploaded_at' => 'datetime',
    'captured_at' => 'datetime',
    'is_primary' => 'boolean',
    'gps_data' => GpsData::class, // Changed
    'metadata' => 'array',
];
```

#### [NEW] [MigrateSpecificationsToValueObjects.php](file:///Users/andri/Documents/projects/sigap/database/migrations/2025_11_22_000001_migrate_specifications_to_value_objects.php)

Data migration command to normalize existing JSON data.

---

### Phase 3: Trait Extraction
- [ ] Create HasLifetimeTracking trait
- [ ] Extract lifetime methods to trait
- [ ] Create HasFiles trait (for polymorphic files)
- [ ] Update Asset model to use traits/HasLifetimeTracking.php)

```php
<?php

namespace App\Models\Concerns;

use App\Enums\UsageUnit;

trait HasLifetimeTracking
{
    /**
     * Calculate lifetime percentage (current/expected).
     */
    public function getLifetimePercentage(): ?float
    {
        $expected = $this->expected_lifetime_value;
        if (!$expected || $expected <= 0) {
            return null;
        }

        $current = $this->getCurrentLifetime();
        if ($current === null) {
            return null;
        }

        return min(100, ($current / $expected) * 100);
    }

    /**
     * Calculate remaining lifetime.
     */
    public function getRemainingLifetime(): ?float
    {
        $expected = $this->expected_lifetime_value;
        if (!$expected) {
            return null;
        }

        $current = $this->getCurrentLifetime();
        if ($current === null) {
            return null;
        }

        return max(0, $expected - $current);
    }

    /**
     * Get current lifetime value.
     */
    public function getCurrentLifetime(): ?float
    {
        // If disposed, return actual lifetime
        if ($this->disposed_date && $this->actual_lifetime_value !== null) {
            return $this->actual_lifetime_value;
        }

        // If not disposed, calculate current lifetime
        if (!$this->lifetime_unit) {
            return null;
        }

        $unit = $this->lifetime_unit;
        if ($unit->isUsageBased()) {
            // For usage-based, need installed_usage_value
            if ($this->installed_usage_value === null) {
                return null;
            }
            // Current usage would need to be tracked separately
            return null;
        } else {
            // Date-based: days since installed_date or purchase_date
            $startDate = $this->installed_date ?? $this->purchase_date;
            if (!$startDate) {
                return null;
            }
            return $startDate->diffInDays(now());
        }
    }

    /**
     * Check if lifetime tracking is configured.
     */
    public function hasLifetimeTracking(): bool
    {
        return $this->lifetime_unit !== null && $this->expected_lifetime_value !== null;
    }

    /**
     * Get lifetime status (healthy, warning, critical).
     */
    public function getLifetimeStatus(): ?string
    {
        $percentage = $this->getLifetimePercentage();
        
        if ($percentage === null) {
            return null;
        }

        return match (true) {
            $percentage >= 90 => 'critical',
            $percentage >= 70 => 'warning',
            default => 'healthy',
        };
    }
}
```

#### [MODIFY] [Asset.php](file:///Users/andri/Documents/projects/sigap/app/Models/Asset.php)

Update to use trait:
```php
use App\Models\Concerns\HasLifetimeTracking;

final class Asset extends Model
{
    use HasLifetimeTracking;
    
    // Remove getLifetimePercentage, getRemainingLifetime, getCurrentLifetime methods
    // They're now in the trait
}
```

---

### Phase 4: Component Model Separation

**Most significant change** - separate Asset and Component concerns.

#### [NEW] [create_asset_components_table.php](file:///Users/andri/Documents/projects/sigap/database/migrations/2025_11_22_000002_create_asset_components_table.php)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained()->onDelete('cascade');
            $table->foreignId('component_asset_id')->constrained('assets')->onDelete('cascade');
            $table->string('component_type'); // consumable, replaceable, integral
            $table->date('installed_date')->nullable();
            $table->decimal('installed_usage_value', 15, 2)->nullable();
            $table->date('removed_date')->nullable();
            $table->decimal('removed_usage_value', 15, 2)->nullable();
            $table->string('removal_reason')->nullable();
            $table->text('installation_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['asset_id', 'is_active']);
            $table->index(['component_asset_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_components');
    }
};
```

#### [NEW] [AssetComponent.php](file:///Users/andri/Documents/projects/sigap/app/Models/AssetComponent.php)

```php
<?php

namespace App\Models;

use App\Enums\ComponentType;
use App\Models\Concerns\HasLifetimeTracking;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class AssetComponent extends Model
{
    use HasLifetimeTracking;

    protected $fillable = [
        'asset_id',
        'component_asset_id',
        'component_type',
        'installed_date',
        'installed_usage_value',
        'removed_date',
        'removed_usage_value',
        'removal_reason',
        'installation_notes',
        'is_active',
    ];

    protected $casts = [
        'installed_date' => 'date',
        'removed_date' => 'date',
        'installed_usage_value' => 'decimal:2',
        'removed_usage_value' => 'decimal:2',
        'component_type' => ComponentType::class,
        'is_active' => 'boolean',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function component(): BelongsTo
    {
        return $this->belongsTo(Asset::class, 'component_asset_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRemoved($query)
    {
        return $query->where('is_active', false);
    }

    public function remove(?string $reason = null, ?float $usageValue = null): void
    {
        $this->update([
            'is_active' => false,
            'removed_date' => now(),
            'removed_usage_value' => $usageValue,
            'removal_reason' => $reason,
        ]);
    }

    public function getLifetimeDays(): ?int
    {
        if (!$this->installed_date) {
            return null;
        }

        $endDate = $this->removed_date ?? now();
        return $this->installed_date->diffInDays($endDate);
    }
}
```

#### [MODIFY] [Asset.php](file:///Users/andri/Documents/projects/sigap/app/Models/Asset.php)

Update relationships:
```php
// Remove: parent_asset_id, childAssets, parentAsset relationships

/**
 * Get components attached to this asset.
 */
public function components(): HasMany
{
    return $this->hasMany(AssetComponent::class);
}

/**
 * Get active components only.
 */
public function activeComponents(): HasMany
{
    return $this->components()->where('is_active', true);
}

/**
 * Get installations where this asset was used as a component.
 */
public function installations(): HasMany
{
    return $this->hasMany(AssetComponent::class, 'component_asset_id');
}

/**
 * Check if this asset is currently installed as a component.
 */
public function isInstalledAsComponent(): bool
{
    return $this->installations()->active()->exists();
}

/**
 * Get current installation if this asset is a component.
 */
public function currentInstallation(): ?AssetComponent
{
    return $this->installations()->active()->first();
}
```

#### [MODIFY] [AssetComponentService.php](file:///Users/andri/Documents/projects/sigap/app/Services/AssetComponentService.php)

Refactor to use AssetComponent model:
```php
public function attachComponent(
    Asset $parent,
    Asset $component,
    ComponentType $componentType,
    ?\DateTime $installedDate = null,
    ?float $installedUsageValue = null,
    ?string $notes = null
): AssetComponent {
    // Validation logic remains similar
    
    return AssetComponent::create([
        'asset_id' => $parent->id,
        'component_asset_id' => $component->id,
        'component_type' => $componentType,
        'installed_date' => $installedDate,
        'installed_usage_value' => $installedUsageValue,
        'installation_notes' => $notes,
        'is_active' => true,
    ]);
}
```

#### [NEW] [MigrateToAssetComponents.php](file:///Users/andri/Documents/projects/sigap/database/migrations/2025_11_22_000003_migrate_to_asset_components.php)

Data migration to move existing parent_asset_id relationships to asset_components table.

---

### Phase 5: Event-Driven Architecture

Decouple status change logic using Laravel events.

#### [NEW] [AssetDisposed.php](file:///Users/andri/Documents/projects/sigap/app/Events/AssetDisposed.php)

```php
<?php

namespace App\Events;

use App\Models\Asset;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssetDisposed
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Asset $asset,
        public ?string $reason = null,
        public ?int $workOrderId = null
    ) {}
}
```

#### [NEW] [AssetStatusChanged.php](file:///Users/andri/Documents/projects/sigap/app/Events/AssetStatusChanged.php)

```php
<?php

namespace App\Events;

use App\Models\Asset;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AssetStatusChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Asset $asset,
        public string $oldStatus,
        public string $newStatus
    ) {}
}
```

#### [NEW] [UpdateAssetLifetime.php](file:///Users/andri/Documents/projects/sigap/app/Listeners/UpdateAssetLifetime.php)

```php
<?php

namespace App\Listeners;

use App\Events\AssetDisposed;
use App\Services\AssetLifetimeService;

class UpdateAssetLifetime
{
    public function __construct(
        private readonly AssetLifetimeService $lifetimeService
    ) {}

    public function handle(AssetDisposed $event): void
    {
        $this->lifetimeService->updateAssetLifetimeOnDisposal($event->asset);
    }
}
```

#### [NEW] [NotifyAssetStakeholders.php](file:///Users/andri/Documents/projects/sigap/app/Listeners/NotifyAssetStakeholders.php)

```php
<?php

namespace App\Listeners;

use App\Events\AssetStatusChanged;
use App\Notifications\AssetStatusChangedNotification;

class NotifyAssetStakeholders
{
    public function handle(AssetStatusChanged $event): void
    {
        // Notify assigned user
        if ($event->asset->user) {
            $event->asset->user->notify(
                new AssetStatusChangedNotification($event->asset, $event->oldStatus, $event->newStatus)
            );
        }

        // Notify department head
        if ($event->asset->department) {
            // Implementation depends on your Department model
        }
    }
}
```

#### [MODIFY] [EventServiceProvider.php](file:///Users/andri/Documents/projects/sigap/app/Providers/EventServiceProvider.php)

Register event listeners:
```php
protected $listen = [
    AssetDisposed::class => [
        UpdateAssetLifetime::class,
        NotifyAssetStakeholders::class,
    ],
    AssetStatusChanged::class => [
        NotifyAssetStakeholders::class,
    ],
];
```

#### [MODIFY] [Asset.php](file:///Users/andri/Documents/projects/sigap/app/Models/Asset.php)

Add event dispatching:
```php
protected static function booted(): void
{
    static::updating(function (Asset $asset) {
        if ($asset->isDirty('status')) {
            AssetStatusChanged::dispatch(
                $asset,
                $asset->getOriginal('status'),
                $asset->status
            );
        }
        
        if ($asset->isDirty('disposed_date') && $asset->disposed_date !== null) {
            AssetDisposed::dispatch(
                $asset,
                $asset->disposal_reason,
                $asset->disposal_work_order_id
            );
        }
    });
}
```

---

### Phase 6: State Machine Implementation

Enforce valid status transitions with a state machine.

#### Install Package

```bash
composer require spatie/laravel-model-states
```

#### [NEW] [AssetStatus.php](file:///Users/andri/Documents/projects/sigap/app/States/AssetStatus.php)

```php
<?php

namespace App\States;

use Spatie\ModelStates\State;
use Spatie\ModelStates\StateConfig;

abstract class AssetStatus extends State
{
    abstract public function color(): string;
    abstract public function icon(): string;

    public static function config(): StateConfig
    {
        return parent::config()
            ->default(Operational::class)
            ->allowTransition(Operational::class, Down::class)
            ->allowTransition(Operational::class, Maintenance::class)
            ->allowTransition(Operational::class, Disposed::class)
            ->allowTransition(Down::class, Maintenance::class)
            ->allowTransition(Down::class, Operational::class)
            ->allowTransition(Down::class, Disposed::class)
            ->allowTransition(Maintenance::class, Operational::class)
            ->allowTransition(Maintenance::class, Down::class)
            ->allowTransition(Maintenance::class, Disposed::class);
    }
}
```

#### [NEW] [Operational.php](file:///Users/andri/Documents/projects/sigap/app/States/Operational.php)

```php
<?php

namespace App\States;

class Operational extends AssetStatus
{
    public function color(): string
    {
        return 'green';
    }

    public function icon(): string
    {
        return 'check-circle';
    }
}
```

#### [NEW] [Down.php](file:///Users/andri/Documents/projects/sigap/app/States/Down.php)

Similar structure for Down, Maintenance, Disposed states.

#### [MODIFY] [Asset.php](file:///Users/andri/Documents/projects/sigap/app/Models/Asset.php)

```php
use Spatie\ModelStates\HasStates;

final class Asset extends Model
{
    use HasStates;

    protected $casts = [
        'status' => AssetStatus::class,
        // ... other casts
    ];
}
```

#### [MODIFY] [AssetController.php](file:///Users/andri/Documents/projects/sigap/app/Http/Controllers/AssetController.php)

Update to use state transitions:
```php
// Instead of: $asset->update(['status' => 'disposed']);
// Use: $asset->status->transitionTo(Disposed::class);
```

---

### Phase 7: Polymorphic File Relationships
- [ ] Create FileCategory enum
- [ ] Create unified File model
- [ ] Create HasFiles trait
- [ ] Migrate AssetPhoto to File (category='photo')
- [ ] Migrate WorkOrderPhoto to File (category='photo')
- [ ] Migrate CleaningSubmission photos to File
- [ ] Migrate CleaningRequest photo to File
- [ ] Implement AssetDocument with File (category='document')
- [ ] Update controllers and servicesgap/database/migrations/2025_11_22_000004_create_files_table.php)

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();
            $table->morphs('fileable'); // fileable_id, fileable_type
            $table->string('path'); // S3 path
            $table->string('filename'); // Original filename
            $table->string('mime_type'); // image/jpeg, application/pdf, etc.
            $table->integer('file_size'); // bytes
            $table->string('file_category')->default('general'); // photo, document, video, etc.
            $table->string('type')->nullable(); // Context: before, after, manual, warranty, etc.
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_primary')->default(false); // For photos
            $table->datetime('uploaded_at');
            $table->datetime('captured_at')->nullable(); // For photos
            $table->foreignId('uploaded_by')->nullable()->constrained('users');
            $table->json('gps_data')->nullable(); // For photos with location
            $table->json('metadata')->nullable(); // EXIF, dimensions, etc.
            $table->timestamps();
            
            $table->index(['fileable_type', 'fileable_id']);
            $table->index(['file_category', 'mime_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
```

#### [NEW] [FileCategoryEnum.php](file:///Users/andri/Documents/projects/sigap/app/Enums/FileCategory.php)

```php
<?php

namespace App\Enums;

enum FileCategory: string
{
    case Photo = 'photo';
    case Document = 'document';
    case Video = 'video';
    case Audio = 'audio';
    case Spreadsheet = 'spreadsheet';
    case General = 'general';

    public static function fromMimeType(string $mimeType): self
    {
        return match (true) {
            str_starts_with($mimeType, 'image/') => self::Photo,
            str_starts_with($mimeType, 'video/') => self::Video,
            str_starts_with($mimeType, 'audio/') => self::Audio,
            in_array($mimeType, ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document']) => self::Document,
            in_array($mimeType, ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']) => self::Spreadsheet,
            default => self::General,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Photo => 'Photo',
            self::Document => 'Document',
            self::Video => 'Video',
            self::Audio => 'Audio',
            self::Spreadsheet => 'Spreadsheet',
            self::General => 'File',
        };
    }
}
```

#### [NEW] [File.php](file:///Users/andri/Documents/projects/sigap/app/Models/File.php)

```php
<?php

namespace App\Models;

use App\Enums\FileCategory;
use App\ValueObjects\GpsData;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

final class File extends Model
{
    protected $fillable = [
        'fileable_id',
        'fileable_type',
        'path',
        'filename',
        'mime_type',
        'file_size',
        'file_category',
        'type',
        'title',
        'description',
        'is_primary',
        'uploaded_at',
        'captured_at',
        'uploaded_by',
        'gps_data',
        'metadata',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
        'captured_at' => 'datetime',
        'is_primary' => 'boolean',
        'file_category' => FileCategory::class,
        'gps_data' => GpsData::class,
        'metadata' => 'array',
    ];

    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk('s3')->url($this->path);
    }

    // Scopes
    public function scopePhotos($query)
    {
        return $query->where('file_category', FileCategory::Photo);
    }

    public function scopeDocuments($query)
    {
        return $query->where('file_category', FileCategory::Document);
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Helpers
    public function isPhoto(): bool
    {
        return $this->file_category === FileCategory::Photo;
    }

    public function isDocument(): bool
    {
        return $this->file_category === FileCategory::Document;
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
```

#### [NEW] [HasFiles.php](file:///Users/andri/Documents/projects/sigap/app/Models/Concerns/HasFiles.php)

Trait for models that use files:

```php
<?php

namespace App\Models\Concerns;

use App\Models\File;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasFiles
{
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function photos(): MorphMany
    {
        return $this->files()->photos();
    }

    public function documents(): MorphMany
    {
        return $this->files()->documents();
    }

    public function primaryPhoto(): ?File
    {
        return $this->photos()->primary()->first()
            ?? $this->photos()->orderBy('created_at')->first();
    }

    public function getImagePath(): ?string
    {
        return $this->primaryPhoto()?->path;
    }
}
```

#### [MODIFY] [Asset.php](file:///Users/andri/Documents/projects/sigap/app/Models/Asset.php)

```php
use App\Models\Concerns\HasFiles;

final class Asset extends Model
{
    use HasFiles; // Adds files(), photos(), documents(), primaryPhoto()
    
    // Remove old photos() and documents() relationships
    // They're now provided by the trait
}
```

#### [MODIFY] [WorkOrder.php](file:///Users/andri/Documents/projects/sigap/app/Models/WorkOrder.php)

```php
use App\Models\Concerns\HasFiles;

final class WorkOrder extends Model
{
    use HasFiles;
    
    // Can now use: $workOrder->photos(), $workOrder->documents()
}
```

---

### Phase 8: Soft Deletes Refactor

Replace custom disposal logic with Laravel's SoftDeletes.

#### [NEW] [add_soft_deletes_to_assets.php](file:///Users/andri/Documents/projects/sigap/database/migrations/2025_11_22_000005_add_soft_deletes_to_assets.php)

```php
Schema::table('assets', function (Blueprint $table) {
    $table->softDeletes();
    // Keep disposed_date and is_active for backward compatibility during migration
});
```

#### [MODIFY] [Asset.php](file:///Users/andri/Documents/projects/sigap/app/Models/Asset.php)

```php
use Illuminate\Database\Eloquent\SoftDeletes;

final class Asset extends Model
{
    use SoftDeletes;

    // Update scopes
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeDisposed($query)
    {
        return $query->onlyTrashed();
    }
}
```

#### [NEW] [MigrateDisposalToSoftDeletes.php](file:///Users/andri/Documents/projects/sigap/database/migrations/2025_11_22_000006_migrate_disposal_to_soft_deletes.php)

```php
// Copy disposed_date to deleted_at for existing disposed assets
DB::table('assets')
    ->whereNotNull('disposed_date')
    ->update(['deleted_at' => DB::raw('disposed_date')]);
```

---

## Verification Plan

### Automated Tests

**Unit Tests:**
- All value objects (AssetSpecifications, GpsData)
- All traits (HasLifetimeTracking)
- AssetComponent model
- State transitions
- Event dispatching

**Integration Tests:**
- Asset creation with specifications
- Component attachment/detachment workflow
- Photo upload and management
- Disposal workflow with events
- Status transitions with state machine

**Feature Tests:**
- Complete asset lifecycle (create → attach components → maintain → dispose)
- API endpoints (if applicable)
- Controller actions

### Manual Verification

1. **Asset Management:**
   - Create new asset with specifications
   - Upload photos with GPS data
   - Verify value objects work correctly

2. **Component Management:**
   - Attach component to asset
   - View component history
   - Remove component
   - Verify circular reference prevention

3. **Status Transitions:**
   - Test all valid transitions
   - Verify invalid transitions are blocked
   - Check event notifications

4. **Disposal Workflow:**
   - Dispose asset
   - Verify soft delete
   - Check lifetime calculations
   - Verify events fired

5. **Reporting:**
   - Asset lifetime reports
   - Component usage reports
   - Ensure all queries work with new structure

### Performance Testing

- Load test with 10,000+ assets
- Query performance for component hierarchies
- Photo loading performance
- Report generation speed

### Data Integrity Checks

- Verify all existing assets migrated correctly
- Check component relationships
- Validate GPS data conversion
- Ensure no data loss in specifications

---

## Rollback Plan

Each phase should be deployable independently with feature flags:

```php
// config/features.php
return [
    'asset_value_objects' => env('FEATURE_ASSET_VALUE_OBJECTS', false),
    'asset_components_v2' => env('FEATURE_ASSET_COMPONENTS_V2', false),
    'asset_state_machine' => env('FEATURE_ASSET_STATE_MACHINE', false),
    'polymorphic_photos' => env('FEATURE_POLYMORPHIC_PHOTOS', false),
    'asset_soft_deletes' => env('FEATURE_ASSET_SOFT_DELETES', false),
];
```

If issues arise:
1. Disable feature flag
2. Roll back migration if needed
3. Fix issues in development
4. Re-deploy with fixes

---

## Timeline Estimate

| Phase | Duration | Dependencies |
|-------|----------|--------------|
| Phase 1: Foundation | 3 days | None |
| Phase 2: Value Objects | 2 days | Phase 1 |
| Phase 3: Traits | 1 day | Phase 1 |
| Phase 4: Components | 5 days | Phase 1, 2, 3 |
| Phase 5: Events | 2 days | Phase 1 |
| Phase 6: State Machine | 3 days | Phase 1, 5 |
| Phase 7: Polymorphic | 3 days | Phase 1, 2 |
| Phase 8: Soft Deletes | 2 days | Phase 1, 5 |
| Phase 9: Testing | 3 days | All phases |
| Phase 10: Deployment | 2 days | Phase 9 |

**Total: ~26 days (5-6 weeks with buffer)**

Can be parallelized:
- Phases 2, 3, 5, 7 can run concurrently after Phase 1
- Phase 4 and 6 depend on earlier phases
