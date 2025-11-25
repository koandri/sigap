# Models Using Photos/Documents - Analysis

## Current State

### Models with Photo Functionality

#### 1. **AssetPhoto** ✅ (Primary Candidate)
- **Table**: `asset_photos`
- **Storage**: S3 (`photo_path`)
- **Features**:
  - Multiple photos per asset
  - Primary photo selection (`is_primary`)
  - GPS data (`gps_data` - JSON)
  - EXIF metadata (`metadata` - JSON)
  - Capture timestamp (`captured_at`)
  - Upload tracking (`uploaded_by`, `uploaded_at`)
- **Relationships**: `belongsTo(Asset)`, `belongsTo(User, 'uploaded_by')`
- **Current Usage**: Asset management system

#### 2. **WorkOrderPhoto** ✅ (Primary Candidate)
- **Table**: `work_order_photos`
- **Storage**: Local storage (`photo_path`)
- **Features**:
  - Multiple photos per work order
  - Photo type classification (`photo_type`)
  - Caption support (`caption`)
  - Upload tracking (`uploaded_by`)
- **Relationships**: `belongsTo(WorkOrder)`, `belongsTo(User, 'uploaded_by')`
- **Current Usage**: Maintenance work orders
- **Note**: Uses `asset('storage/')` instead of S3

#### 3. **CleaningSubmission** ⚠️ (Needs Refactor)
- **Table**: `cleaning_submissions`
- **Storage**: JSON arrays (`before_photo`, `after_photo`)
- **Features**:
  - Before/after photo pairs
  - Stored as JSON with watermark metadata
  - Submission tracking
- **Relationships**: `belongsTo(CleaningTask)`, `belongsTo(User, 'submitted_by')`
- **Current Usage**: Cleaning task verification
- **Issue**: Photos stored as JSON arrays, not proper file references

#### 4. **CleaningRequest** ⚠️ (Needs Refactor)
- **Table**: `cleaning_requests`
- **Storage**: Single field (`photo`)
- **Features**:
  - Single photo per request
  - No metadata tracking
  - Simple string field
- **Relationships**: `belongsTo(Location)`, `belongsTo(User)`
- **Current Usage**: User-submitted cleaning/repair requests
- **Issue**: No support for multiple photos or metadata

---

### Models with Document Functionality

#### 5. **AssetDocument** ❌ (Empty Model)
- **Table**: `asset_documents`
- **Storage**: `file_path` (based on migration)
- **Features**: Model is empty (no implementation)
- **Status**: **NOT IMPLEMENTED** - just a placeholder
- **Recommendation**: Skip this, implement with polymorphic Documents

#### 6. **DocumentVersion** 📄 (Different Purpose)
- **Table**: `document_versions`
- **Storage**: `file_path` (PDF files)
- **Features**:
  - Version control for documents
  - Approval workflow
  - File type tracking (`file_type`)
  - NCR paper support (`is_ncr_paper`)
- **Relationships**: `belongsTo(Document)`, `hasMany(DocumentVersionApproval)`
- **Current Usage**: Document management system (SOPs, forms, etc.)
- **Note**: This is for **versioned PDF documents**, not attachments

#### 7. **PrintedForm** 📄 (Different Purpose)
- **Table**: `printed_forms`
- **Storage**: `scanned_file_path` (scanned PDFs)
- **Features**:
  - Physical form tracking
  - Scanned copy storage
  - Circulation status
  - Physical location tracking
- **Relationships**: `belongsTo(DocumentVersion)`, `belongsTo(User, 'issued_to')`
- **Current Usage**: Physical form management
- **Note**: This is for **scanned forms**, not general attachments

---

## Recommendations

### ✅ Models to Refactor with Polymorphic Files

| Model | Priority | File Types | Complexity |
|-------|----------|------------|------------|
| **AssetPhoto** | HIGH | Photos (with GPS, metadata) | Medium |
| **WorkOrderPhoto** | HIGH | Photos (before/after/progress) | Medium |
| **CleaningSubmission** | MEDIUM | Photos (before/after pairs) | High |
| **CleaningRequest** | MEDIUM | Photos | Low |
| **AssetDocument** | HIGH | Documents (manuals, warranties) | Low |
| **WorkOrder** | MEDIUM | Documents (reports, invoices) | Medium |

### ❌ Models to EXCLUDE

| Model | Reason |
|-------|--------|
| **DocumentVersion** | Specialized for versioned PDF documents with approval workflow |
| **PrintedForm** | Specialized for scanned physical forms with circulation tracking |
| **FormSubmission** | Uses dynamic form fields, not file attachments |

---

## Proposed Polymorphic Structure

### File Model (Unified Polymorphic)

**Why "File" instead of separate Photo/Document models?**
- ✅ Simpler architecture - one model to maintain
- ✅ More flexible - a "photo" is just a file with image MIME type
- ✅ Easier queries - no need to union photos and documents
- ✅ Natural grouping - all attachments in one place
- ✅ Future-proof - supports videos, audio, etc. without new models

```php
Schema::create('files', function (Blueprint $table) {
    $table->id();
    $table->morphs('fileable'); // fileable_id, fileable_type
    $table->string('path'); // S3 path
    $table->string('filename'); // Original filename
    $table->string('mime_type'); // image/jpeg, application/pdf, etc.
    $table->integer('file_size'); // bytes
    $table->string('file_category')->default('general'); // 'photo', 'document', 'video', 'audio'
    $table->string('type')->nullable(); // Context-specific: 'before', 'after', 'manual', 'warranty', etc.
    $table->string('title')->nullable();
    $table->text('description')->nullable();
    $table->boolean('is_primary')->default(false); // For photos
    $table->datetime('uploaded_at');
    $table->datetime('captured_at')->nullable(); // For photos
    $table->foreignId('uploaded_by')->nullable()->constrained('users');
    $table->json('gps_data')->nullable(); // For photos with location
    $table->json('metadata')->nullable(); // EXIF, watermark, dimensions, etc.
    $table->timestamps();
    
    $table->index(['fileable_type', 'fileable_id']);
    $table->index(['file_category', 'mime_type']);
});
```

**File Categories (auto-detected from MIME type):**
- `photo` - image/jpeg, image/png, image/gif, image/webp
- `document` - application/pdf, application/msword, text/plain
- `spreadsheet` - application/vnd.ms-excel, application/vnd.openxmlformats
- `video` - video/mp4, video/quicktime
- `audio` - audio/mpeg, audio/wav
- `general` - anything else

**Can be used by:**
- `Asset` (photos with GPS, manuals, warranties, certificates)
- `WorkOrder` (before/after photos, reports, invoices)
- `CleaningSubmission` (before/after photos)
- `CleaningRequest` (photos)
- `MaintenanceLog` (service reports, photos)
- `Location` (photos)
- `Department` (photos, documents)
- Any future model that needs file attachments

---

## Migration Strategy

### Phase 1: Create Polymorphic File Table
1. Create `files` table with all fields
2. Create File model with scopes for photos/documents
3. Create FileCategoryEnum for type safety

### Phase 2: Migrate AssetPhoto
1. Copy data from `asset_photos` to `files`
2. Set `fileable_type` = 'App\Models\Asset'
3. Set `file_category` = 'photo'
4. Detect `mime_type` from file extension
5. Update Asset model: `files()` and `photos()` relationships
6. Test thoroughly
7. Drop `asset_photos` table

### Phase 3: Migrate WorkOrderPhoto
1. Copy data from `work_order_photos` to `files`
2. Migrate from local storage to S3
3. Set `fileable_type` = 'App\Models\WorkOrder'
4. Set `file_category` = 'photo'
5. Map `photo_type` to `type` field
6. Update WorkOrder model
7. Drop `work_order_photos` table

### Phase 4: Migrate CleaningSubmission
1. Parse JSON `before_photo` and `after_photo` arrays
2. Create File records with `file_category` = 'photo', `type` = 'before'/'after'
3. Extract watermark metadata to `metadata` JSON
4. Update CleaningSubmission model
5. Remove `before_photo` and `after_photo` columns

### Phase 5: Migrate CleaningRequest
1. Create File records from `photo` field
2. Set `file_category` = 'photo'
3. Update CleaningRequest model
4. Remove `photo` column

### Phase 6: Implement AssetDocument
1. Create File records for asset documents
2. Set `file_category` = 'document'
3. Update Asset model with `documents()` scope
4. Build upload/download UI

---

## Storage Standardization

### Current Issues:
- AssetPhoto uses S3
- WorkOrderPhoto uses local storage
- CleaningSubmission stores paths in JSON
- Inconsistent URL generation

### Proposed Solution:
- **All photos → S3** with consistent path structure
- **All documents → S3** with consistent path structure
- **Path format**: `{model}/{id}/{type}/{filename}`
  - Example: `assets/123/photos/IMG_001.jpg`
  - Example: `work-orders/456/photos/before_001.jpg`
  - Example: `assets/123/documents/manual.pdf`

---

## Benefits of Unified File Model

### ✅ Advantages:
1. **Simplicity**: Single File model instead of Photo + Document
2. **Code Reusability**: One upload/download/storage system for all file types
3. **Consistency**: Same API for photos, documents, videos, etc.
4. **Extensibility**: Easy to add files to any model, supports any file type
5. **Maintenance**: Fix bugs once, benefits all file types and models
6. **Features**: GPS, metadata, watermarking available for all files
7. **Storage**: Centralized S3 management
8. **Queries**: Simpler - get all files, or filter by category/MIME type
9. **Future-proof**: Videos, audio, archives supported without schema changes

### ⚠️ Considerations:
1. **Migration Complexity**: Need careful data migration
2. **Backward Compatibility**: Ensure existing code works during transition
3. **Performance**: Polymorphic queries can be slower (use eager loading)
4. **Type Safety**: Need to validate `photoable_type` values

---

## Next Steps

**User Decision Required:**

Which models should be refactored to use polymorphic Photos/Documents?

### Recommended Approach:
- ✅ **AssetPhoto** → File (polymorphic, category='photo')
- ✅ **WorkOrderPhoto** → File (polymorphic, category='photo')
- ✅ **CleaningSubmission** → File (polymorphic, category='photo', before/after)
- ✅ **CleaningRequest** → File (polymorphic, category='photo')
- ✅ **AssetDocument** → File (polymorphic, category='document')
- ❌ **DocumentVersion** → Keep as-is (specialized versioned documents)
- ❌ **PrintedForm** → Keep as-is (specialized scanned forms)
