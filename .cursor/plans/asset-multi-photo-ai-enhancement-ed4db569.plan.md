<!-- ed4db569-ac9d-4b2d-81ff-28bb55aa46ba 66667750-2813-4889-85d1-6da11941ed62 -->
# Asset Multi-Photo AI Enhancement Plan

## Overview

Enhance the asset management system to support multiple photos per asset with timestamps, implement mobile camera capture with batch AI-powered recognition, and automatically fetch specifications from the web using Firecrawl API.

## Database Changes

### 1. Create Asset Photos Table

- **File**: `database/migrations/YYYY_MM_DD_HHMMSS_create_asset_photos_table.php`
- Create `asset_photos` table with:
  - `id` (primary key)
  - `asset_id` (foreign key to assets, nullable initially - photos can be uploaded before asset creation)
  - `photo_path` (string, S3 path)
  - `uploaded_at` (timestamp - when uploaded to system, UTC+7 timezone)
  - `captured_at` (timestamp - from EXIF if available, otherwise uploaded_at, UTC+7 timezone)
  - `is_primary` (boolean, default false)
  - `uploaded_by` (foreign key to users)
  - `gps_data` (json, nullable - latitude, longitude)
  - `metadata` (json, nullable - EXIF data, file size, dimensions)
  - `timestamps`

### 2. Migration: Deprecate image_path field

- **File**: `database/migrations/YYYY_MM_DD_HHMMSS_deprecate_assets_image_path.php`
- **Since not in production**: Keep `image_path` as nullable for now, but mark as deprecated
  - Add comment to migration explaining it's deprecated
  - System will use `asset_photos` table going forward
  - Add helper method `Asset->getImagePath()` that checks primary photo first, falls back to `image_path` if no photos exist

## Models

### 3. Create AssetPhoto Model

- **File**: `app/Models/AssetPhoto.php`
- Relationships:
  - `belongsTo(Asset::class)`
  - `belongsTo(User::class, 'uploaded_by')`
- Casts: `captured_at`, `uploaded_at` as datetime, `gps_data`, `metadata` as array
- Accessor for full S3 URL
- Scope: `primary()` - returns primary photos

### 4. Update Asset Model

- **File**: `app/Models/Asset.php`
- Add relationship: `hasMany(AssetPhoto::class)`
- Add method: `primaryPhoto()` - returns primary photo or first photo
- Add method: `getImagePath()` - returns primary photo path or falls back to `image_path` field

## Services

### 5. Create OpenRouterService

- **File**: `app/Services/OpenRouterService.php`
- Methods:
  - `analyzeAssetImages(array $imagesBase64): array` - Analyze multiple images in batch and extract:
    - Asset name (best match across all photos)
    - Suggested category (match against existing AssetCategory names)
    - Manufacturer (if visible in any photo)
    - Model number (if visible in any photo)
    - Serial number (if visible in any photo)
  - Use model: `google/gemini-2.0-flash-exp:free` or `google/gemini-flash-1.5`
  - Send all images in single API call for context
  - Return structured JSON with confidence scores and per-photo analysis
  - Handle API errors gracefully

### 6. Create FirecrawlService

- **File**: `app/Services/FirecrawlService.php`
- Methods:
  - `searchSpecifications(string $manufacturer, string $model): array` - Search web for specifications
    - Search query: "{manufacturer} {model} specifications" or "{manufacturer} {model} technical specifications"
    - Only search if both manufacturer AND model are provided
  - `extractSpecifications(string $url): array` - Extract structured data from URL
  - Use Firecrawl search API to find product pages
  - Use Firecrawl scrape API with JSON schema to extract:
    - Technical specifications (voltage, power, dimensions, weight, etc.)
    - Product description
    - Warranty information
  - Return structured JSON matching asset specifications format

## Controllers

### 7. Update AssetController

- **File**: `app/Http/Controllers/AssetController.php`
- Add methods:
  - `createMobile(): View` - Mobile-optimized asset creation page
  - `analyzeImages(Request $request): JsonResponse` - Analyze multiple uploaded images via OpenRouter (batch)
  - `fetchSpecifications(Request $request): JsonResponse` - Fetch specs via Firecrawl (requires manufacturer + model)
  - `storeMobile(Request $request): RedirectResponse` - Store asset from mobile flow with multiple photos
- Update `store()` method to handle multiple photos
- Update `update()` method to handle photo management
- Add photo management methods:
  - `setPrimaryPhoto(Asset $asset, AssetPhoto $photo): JsonResponse`
  - `deletePhoto(AssetPhoto $photo): JsonResponse`

## Routes

### 9. Update routes/web.php

- Add routes:
  - `GET /assets/create-mobile` - Mobile creation page
  - `POST /assets/analyze-images` - Batch AI image analysis endpoint (accepts array of base64 images)
  - `POST /assets/fetch-specifications` - Firecrawl specifications endpoint
  - `POST /assets/{asset}/photos` - Upload photo to asset
  - `PUT /assets/{asset}/photos/{photo}/primary` - Set primary photo
  - `DELETE /assets/{asset}/photos/{photo}` - Delete photo
- Keep existing routes for backward compatibility

## Views

### 10. Create Mobile Asset Creation View

- **File**: `resources/views/options/assets/create-mobile.blade.php`
- Features:
  - Full-screen camera interface using existing live-photo component pattern
  - Capture button for rear camera (allows multiple captures)
  - Photo gallery preview showing all captured photos (with remove option)
  - "Analyze All Photos with AI" button (enabled when at least 1 photo uploaded)
  - Loading indicators during batch AI analysis with progress
  - Auto-filled form fields (name, category, manufacturer, model) after analysis
  - "Fetch Specifications" button (enabled only when both manufacturer AND model are detected)
  - Form fields for remaining asset details
  - Submit button (creates asset and associates all uploaded photos)
- Use existing live-photo component JavaScript patterns from `resources/views/components/form-fields/live-photo.blade.php`
- Photo upload flow: Capture → Store temporarily (base64 in memory) → Analyze on demand → Submit all together

### 11. Update Standard Create View

- **File**: `resources/views/options/assets/create.blade.php`
- Add:
  - Link/button to "Create via Mobile Camera"
  - Multiple photo upload support
  - Photo gallery preview
  - Primary photo selection

### 12. Update Asset Show/Edit Views

- **Files**: `resources/views/options/assets/show.blade.php`, `resources/views/options/assets/edit.blade.php`
- Display photo gallery with primary photo highlighted
- Allow photo management (upload, delete, set primary)
- Show photo timestamps (captured_at and uploaded_at) in UTC+7

## JavaScript/Assets

### 13. Create Asset Mobile JS

- **File**: `resources/js/asset-mobile.js` or inline in blade
- Functions:
  - Camera initialization and capture (multiple photos)
  - Base64 image handling and storage in memory
  - AJAX calls to analyze-images endpoint (sends array of all photos)
  - Auto-fill form fields from AI response
  - AJAX calls to fetch-specifications endpoint (only if manufacturer + model available)
  - Form validation and submission
  - GPS coordinate capture (reuse from live-photo component)
  - Photo gallery management (add, remove, reorder)

## Photo Processing

### 14. Photo Upload Handler

- **File**: Update `AssetController@store` and new methods
- Process base64 images:
  - Decode base64 to binary
  - Extract EXIF data using PHP EXIF extension or Intervention Image
  - Extract `captured_at` timestamp from EXIF DateTimeOriginal or DateTime
    - Parse EXIF timestamp and convert to UTC+7 timezone (Asia/Jakarta)
    - If EXIF extraction fails, use current time in UTC+7
  - Extract GPS coordinates from EXIF
  - Resize images (max 1920x1080, maintain aspect ratio)
  - Store to S3: `assets/{asset_id}/photos/{timestamp}_{filename}.jpg`
  - Create AssetPhoto record with:
    - `captured_at` from EXIF (UTC+7) or `uploaded_at` if EXIF fails
    - `uploaded_at` as current time (UTC+7)
    - `gps_data` from EXIF (latitude, longitude)
    - `metadata` (dimensions, file size, EXIF data, timezone info)
- Set first uploaded photo as primary if no primary exists
- Handle multiple photos in batch during asset creation

## AI Integration Details

### 15. OpenRouter Batch Image Analysis

- **Workflow**: User uploads multiple photos → clicks "Analyze All Photos" → System analyzes all photos together
- Analyze all uploaded images in a single API call (multi-image context)
- Return aggregated JSON from all photos:
```json
{
  "suggested_name": "string (best match across all photos)",
  "suggested_category": "string (match against existing categories)",
  "manufacturer": "string or null (from any photo)",
  "model": "string or null (from any photo)",
  "serial_number": "string or null (from any photo)",
  "confidence": "float 0-1",
  "photo_analysis": [
    {
      "photo_index": 0,
      "detected_items": ["manufacturer label", "model number", "specifications plate"]
    }
  ]
}
```

- Use structured output or JSON mode if available
- Match category names against existing AssetCategory records
- Prompt should instruct AI to look for: name plates, model numbers, serial numbers, specifications labels, brand logos across all provided images

### 16. Firecrawl Specification Extraction

- **Search Strategy**: Use "Manufacturer + Model" combination for accurate results
- Search query: "{manufacturer} {model} specifications" or "{manufacturer} {model} technical specifications"
- Only proceed if both manufacturer AND model are provided
- Extract using JSON schema matching asset specifications structure:
  - voltage, power, weight, dimensions, etc.
- Fallback to markdown extraction if structured fails
- If manufacturer/model not found, skip Firecrawl search (don't search with partial data)

## Configuration

### 17. Environment Variables

Add to `.env`:

```
OPENROUTER_API_KEY=your_openrouter_api_key
OPENROUTER_MODEL=google/gemini-2.0-flash-exp:free
FIRECRAWL_API_KEY=your_firecrawl_api_key
```

**Note**: Timezone is hardcoded as `'Asia/Jakarta'` (UTC+7) in the photo processing code, not stored in .env.

### 18. Update config/services.php

Add service configurations:

```php
'openrouter' => [
    'api_key' => env('OPENROUTER_API_KEY'),
    'base_url' => 'https://openrouter.ai/api/v1',
    'model' => env('OPENROUTER_MODEL', 'google/gemini-2.0-flash-exp:free'),
],

'firecrawl' => [
    'api_key' => env('FIRECRAWL_API_KEY'),
    'base_url' => 'https://api.firecrawl.dev/v2',
],
```

## Error Handling

### 19. API Error Handling

- OpenRouter: Log errors, return user-friendly messages, allow manual entry
- Firecrawl: Log errors, continue without specifications, allow manual entry
- Network timeouts: Set appropriate timeouts (30s for OpenRouter, 60s for Firecrawl)
- Rate limiting: Implement basic retry logic with exponential backoff
- Batch analysis: If one photo fails, continue with others and show partial results

## Testing Considerations

### 20. Test Scenarios

- Multiple photo uploads (2-5 photos)
- EXIF timestamp extraction with UTC+7 conversion
- Batch AI analysis with various asset types
- Firecrawl specification fetching with manufacturer + model
- Mobile camera capture on different devices
- Fallback to manual entry when APIs fail
- Photo limits (max 10 photos per asset)

## Additional Recommendations

### 21. Photo Upload Limits

- Max 10 photos per asset to prevent abuse
- Max 5MB per photo, auto-compress if larger
- Batch analysis: Analyze up to 5 photos at once (OpenRouter context limits)

### 22. User Experience Enhancements

- Show progress indicator during batch analysis ("Analyzing photo 2 of 5...")
- Cache AI analysis results temporarily (session) to avoid re-analysis on form errors
- Allow users to manually trigger re-analysis if results are unsatisfactory
- Display confidence scores to users (optional)

### 23. Backward Compatibility

- Keep `image_path` nullable in database
- Update all views to use `asset_photos` table
- Helper method `Asset->getImagePath()` provides fallback for any existing test data

### To-dos

- [ ] Create asset_photos migration table with all required fields (photo_path, uploaded_at, captured_at, is_primary, uploaded_by, gps_data, metadata)
- [ ] Create migration to make assets.image_path nullable for backward compatibility
- [ ] Create AssetPhoto model with relationships and casts
- [ ] Update Asset model to add hasMany(AssetPhoto) relationship and primaryPhoto() method
- [ ] Create OpenRouterService with analyzeAssetImage() method for AI-powered asset recognition
- [ ] Create FirecrawlService with searchSpecifications() and extractSpecifications() methods
- [ ] Add analyzeImage() and fetchSpecifications() API endpoints to AssetController
- [ ] Create createMobile() method and mobile-optimized view for camera-based asset creation
- [ ] Update AssetController store() method to handle multiple photo uploads with EXIF extraction
- [ ] Add photo management methods (setPrimaryPhoto, deletePhoto) to AssetController
- [ ] Create mobile asset creation view with camera capture, AI analysis, and auto-fill functionality
- [ ] Update standard asset create/edit views to support multiple photos and photo gallery
- [ ] Add routes for mobile creation, photo management, and AI analysis endpoints
- [ ] Update config/services.php and .env.example with OpenRouter and Firecrawl API keys