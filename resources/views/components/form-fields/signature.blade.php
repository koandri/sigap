@props(['field', 'value' => '', 'prefillData' => []])

@php
    $fieldValue = old('fields.'.$field->field_code, $prefillData[$field->field_code] ?? $value);
    $width = $field->validation_rules['width'] ?? 400;
    $height = $field->validation_rules['height'] ?? 200;
@endphp

<div class="signature-pad-container">
    <canvas id="{{ $field->field_code }}_canvas" class="signature-canvas" style="width: {{ $width }}px; height: {{ $height }}px; max-width: 100%; display: block; border: 1px solid #dee2e6; border-radius: 0.375rem;" data-original-width="{{ $width }}" data-original-height="{{ $height }}"></canvas>
    <input type="hidden" id="{{ $field->field_code }}" name="fields[{{ $field->field_code }}]" value="{{ $fieldValue }}" {{ $field->is_required ? 'required' : '' }}>
</div>

<div class="mt-2">
    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSignature('{{ $field->field_code }}')">
        <i class="fa-regular fa-eraser"></i> &nbsp;Clear
    </button>
</div>

<style>
/* Responsive signature pad sizing */
@media (min-width: 768px) {
    .signature-canvas {
        max-width: 300px !important;
        max-height: 150px !important;
    }
}

@media (min-width: 992px) {
    .signature-canvas {
        max-width: 250px !important;
        max-height: 125px !important;
    }
}

@media (min-width: 1200px) {
    .signature-canvas {
        max-width: 200px !important;
        max-height: 100px !important;
    }
}

/* Ensure aspect ratio is maintained */
.signature-canvas {
    object-fit: contain;
}
</style>

<script>
// Wait for page to be fully loaded to prevent layout forcing
window.addEventListener('load', function() {
    const canvas = document.getElementById('{{ $field->field_code }}_canvas');
    if (canvas) {
        // Function to resize canvas based on screen size
        function resizeSignatureCanvas() {
            const originalWidth = parseInt(canvas.dataset.originalWidth);
            const originalHeight = parseInt(canvas.dataset.originalHeight);
            const aspectRatio = originalWidth / originalHeight;
            
            let maxWidth, maxHeight;
            
            if (window.innerWidth >= 1200) {
                // Large desktop
                maxWidth = 200;
                maxHeight = 100;
            } else if (window.innerWidth >= 992) {
                // Desktop
                maxWidth = 250;
                maxHeight = 125;
            } else if (window.innerWidth >= 768) {
                // Tablet
                maxWidth = 300;
                maxHeight = 150;
            } else {
                // Mobile - use original size
                maxWidth = originalWidth;
                maxHeight = originalHeight;
            }
            
            // Calculate new dimensions maintaining aspect ratio
            let newWidth = maxWidth;
            let newHeight = maxWidth / aspectRatio;
            
            if (newHeight > maxHeight) {
                newHeight = maxHeight;
                newWidth = maxHeight * aspectRatio;
            }
            
            // Apply new dimensions
            canvas.style.width = newWidth + 'px';
            canvas.style.height = newHeight + 'px';
            
            // Update canvas internal dimensions for proper drawing
            canvas.width = newWidth;
            canvas.height = newHeight;
        }
        
        // Initial resize
        resizeSignatureCanvas();
        
        // Resize on window resize
        window.addEventListener('resize', resizeSignatureCanvas);
    }
});
</script>