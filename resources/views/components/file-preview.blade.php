@props(['answer', 'field'])

@php
    $metadata = $answer->answer_metadata ?? [];
    $isMultiple = $metadata['multiple'] ?? false;
@endphp

<div class="file-preview-container">
    @if($isMultiple && isset($metadata['files']))
        <!-- Multiple files -->
        <div class="row">
            @foreach($metadata['files'] as $index => $file)
                <div class="col-md-6 col-lg-6 mb-3">
                    <div class="card h-100">
                        @php
                            $extension = strtolower($file['extension'] ?? '');
                            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                            $isPdf = $extension === 'pdf';
                            $isDocument = in_array($extension, ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx']);
                        @endphp
                        
                        @if($isImage)
                            <!-- Image preview with Lightbox -->
                            <a href="{{ route('files.preview', [$answer->id, $index]) }}" data-lightbox="gallery-{{ $answer->id }}" data-title="{{ $file['original_name'] }}">
                                <img src="{{ route('files.thumbnail', [$answer->id, $index]) }}?w=400&h=600&mode=fit&q=85" class="card-img-top" alt="{{ $file['original_name'] }}" loading="lazy" style="height: 200px; object-fit: cover; cursor: zoom-in;">
                            </a>
                        @else
                            <!-- File icon -->
                            <div class="card-img-top text-center py-4" style="background: #f8f9fa; height: 200px; display: flex; align-items: center; justify-content: center;">
                                @if($isPdf)
                                    <i class="fa-regular fa-file-pdf text-danger" style="font-size: 4rem;"></i>
                                @elseif(in_array($extension, ['doc', 'docx']))
                                    <i class="fa-regular fa-file-word text-primary" style="font-size: 4rem;"></i>
                                @elseif(in_array($extension, ['xls', 'xlsx']))
                                    <i class="fa-regular fa-file-excel text-success" style="font-size: 4rem;"></i>
                                @elseif(in_array($extension, ['ppt', 'pptx']))
                                    <i class="fa-regular fa-file-ppt text-danger" style="font-size: 4rem;"></i>
                                @elseif(in_array($extension, ['zip', 'rar', '7z']))
                                    <i class="fa-regular fa-file-zip text-warning" style="font-size: 4rem;"></i>
                                @else
                                    <i class="fa-regular fa-file text-secondary" style="font-size: 4rem;"></i>
                                @endif
                            </div>
                        @endif
                        
                        <div class="card-body p-2">
                            <p class="card-text small mb-1 text-truncate" title="{{ $file['original_name'] }}">
                                <strong>{{ $file['original_name'] }}</strong>
                            </p>
                            <p class="card-text small text-muted mb-2">
                                <i class="fa-regular fa-hard-drive"></i>&nbsp;{{ number_format($file['size'] / 1024, 1) }} KB
                                <br>
                                <i class="fa-regular fa-file-code"></i>&nbsp;{{ strtoupper($extension) }}
                            </p>
                            <div class="btn-group btn-group-sm w-100">
                                @if($isImage)
                                    <button type="button"
                                            class="btn btn-outline-primary btn-sm"
                                            onclick="openLightbox('{{ route('files.preview', [$answer->id, $index ?? null]) }}', '{{ $file['original_name'] ?? $metadata['original_name'] ?? 'Image' }}')">
                                        <i class="fa-regular fa-eye"></i>&nbsp;View
                                    </button>
                                @elseif($isPdf)
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="previewPdf('{{ route('files.preview', [$answer->id, $index ?? null]) }}', '{{ $file['original_name'] ?? $metadata['original_name'] ?? 'PDF' }}')">
                                        <i class="fa-regular fa-eye"></i>&nbsp;View Full
                                    </button>
                                @endif
                                
                                <a href="{{ route('files.download', [$answer->id, $index ?? null]) }}" class="btn btn-outline-success btn-sm">
                                    <i class="fa-regular fa-download"></i>&nbsp;Download
                                </a>
                                
                                @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) && $isImage)
                                <a href="{{ route('files.download.original', [$answer->id, $index ?? null]) }}" class="btn btn-outline-warning btn-sm" title="Download without watermark">
                                    <i class="fa-regular fa-download"></i>&nbsp;Download Original
                                </a>
                                @endif
                                
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <!-- Gallery info -->
        <div class="text-center mt-2">
            <small class="text-muted">
                <i class="fa-regular fa-circle-info"></i>
                Total: {{ count($metadata['files']) }} file(s) uploaded
                @php
                    $imageCount = collect($metadata['files'])->filter(function($file) {
                        return in_array(strtolower($file['extension'] ?? ''), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                    })->count();
                @endphp
                @if($imageCount > 0)
                    | Click on images to view in gallery mode
                @endif
            </small>
        </div>
    @else
        <!-- Single file -->
        @php
            $extension = strtolower($metadata['extension'] ?? pathinfo($answer->answer_value, PATHINFO_EXTENSION));
            $isImage = in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
            $isPdf = $extension === 'pdf';
        @endphp
        
        <div class="single-file-preview">
            @if($isImage)
                <!-- Image preview with Lightbox -->
                <div class="mb-3 text-center">
                    <a href="{{ route('files.preview', $answer->id) }}" data-lightbox="single-{{ $answer->id }}" data-title="{{ $metadata['original_name'] ?? 'Image' }}">
                        <img src="{{ route('files.thumbnail', $answer->id) }}?w=600&h=800&mode=fit&q=85" class="img-fluid rounded shadow-sm" alt="{{ $metadata['original_name'] ?? 'Image' }}" style="max-height: 400px; cursor: zoom-in;" loading="lazy">
                    </a>
                    <small class="text-muted d-block mt-2">
                        <i class="fa-regular fa-magnifying-glass-plus"></i>&nbsp;Click image to enlarge
                    </small>
                </div>
            @elseif($isPdf)
                <!-- PDF preview embed -->
                <div class="mb-3">
                    <div class="pdf-preview-container">
                        <div class="ratio ratio-16x9">
                            <iframe src="{{ route('files.preview', $answer->id) }}#toolbar=0" title="PDF Preview" class="border rounded" allowfullscreen>
                            </iframe>
                        </div>
                        <div class="text-center mt-2">
                            <button type="button" class="btn btn-sm btn-outline-primary" onclick="previewPdf('{{ route('files.preview', $answer->id) }}', '{{ $metadata['original_name'] ?? 'PDF Document' }}')">
                                <i class="fa-regular fa-up-right-and-down-left-from-center"></i>&nbsp;Open in Modal
                            </button>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- File info and actions -->
            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                <div>
                    @if($isPdf)
                        <i class="fa-regular fa-file-pdf text-danger me-2" style="font-size: 1.5rem;"></i>
                    @elseif($isImage)
                        <i class="fa-regular fa-file-image text-primary me-2" style="font-size: 1.5rem;"></i>
                    @elseif(in_array($extension, ['doc', 'docx']))
                        <i class="fa-regular fa-file-word text-primary me-2" style="font-size: 1.5rem;"></i>
                    @elseif(in_array($extension, ['xls', 'xlsx']))
                        <i class="fa-regular fa-file-excel text-success me-2" style="font-size: 1.5rem;"></i>
                    @else
                        <i class="fa-regular fa-file text-secondary me-2" style="font-size: 1.5rem;"></i>
                    @endif
                    
                    <span>
                        <strong>{{ $metadata['original_name'] ?? 'File' }}</strong>
                        <br>
                        <small class="text-muted">
                            Size: {{ number_format(($metadata['size'] ?? 0) / 1024, 1) }} KB
                            | Type: {{ strtoupper($extension) }}
                            @if(isset($metadata['mime_type']))
                                | {{ $metadata['mime_type'] }}
                            @endif
                        </small>
                    </span>
                </div>
                <div class="btn-group">
                    @if($isImage)
                        <button type="button"
                                class="btn btn-outline-primary btn-sm"
                                onclick="openLightbox('{{ route('files.preview', [$answer->id, $index ?? null]) }}', '{{ $file['original_name'] ?? $metadata['original_name'] ?? 'Image' }}')">
                            <i class="fa-regular fa-eye"></i>&nbsp;View
                        </button>
                    @elseif($isPdf)
                        <button type="button"
                                class="btn btn-outline-primary btn-sm"
                                onclick="previewPdf('{{ route('files.preview', [$answer->id, $index ?? null]) }}', '{{ $file['original_name'] ?? $metadata['original_name'] ?? 'PDF' }}')">
                            <i class="fa-regular fa-eye"></i>&nbsp;View Full
                        </button>
                    @endif
                    
                    <a href="{{ route('files.download', [$answer->id, $index ?? null]) }}" 
                    class="btn btn-outline-success btn-sm">
                        <i class="fa-regular fa-download"></i>&nbsp;Download
                    </a>
                    
                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) && $isImage)
                        <a href="{{ route('files.download.original', [$answer->id, $index ?? null]) }}" 
                        class="btn btn-outline-warning btn-sm"
                        title="Download without watermark">
                            <i class="fa-regular fa-download"></i>&nbsp;Download Original
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif
</div>

@once
@push('css')

    <style>
    .file-preview-container {
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .file-preview-container .card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: 1px solid #dee2e6;
    }

    .file-preview-container .card:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.15);
    }

    .single-file-preview img {
        transition: transform 0.2s;
        border: 1px solid #dee2e6;
    }

    .single-file-preview img:hover {
        transform: scale(1.02);
        box-shadow: 0 5px 20px rgba(0,0,0,0.2);
    }

    .pdf-preview-container iframe {
        min-height: 500px;
        background: #f8f9fa;
    }

    /* Lightbox customization */
    .lb-data .lb-details {
        width: 100%;
        text-align: center;
    }

    .lb-nav a.lb-prev, .lb-nav a.lb-next {
        opacity: 0.5;
    }

    .lb-nav a.lb-prev:hover, .lb-nav a.lb-next:hover {
        opacity: 1;
    }

    /* File type icon colors */
    .bi-file-earmark-pdf { color: #dc3545; }
    .bi-file-earmark-word { color: #0d6efd; }
    .bi-file-earmark-excel { color: #198754; }
    .bi-file-earmark-ppt { color: #fd7e14; }
    .bi-file-earmark-zip { color: #ffc107; }
    .bi-file-earmark-image { color: #0dcaf0; }
    </style>
@endpush

@push('scripts')
<script>

    // Function to open lightbox programmatically
    function openLightbox(url, title) {
        // Create temporary link and trigger click
        const link = document.createElement('a');
        link.href = url;
        link.setAttribute('data-lightbox', 'temp-lightbox');
        link.setAttribute('data-title', title);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // PDF preview in modal
    function previewPdf(url, title) {
        // Remove existing modal if any
        const existingModal = document.getElementById('pdfModal');
        if (existingModal) {
            existingModal.remove();
        }
        
        const modal = `
            <div class="modal fade" id="pdfModal" tabindex="-1">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="fa-regular fa-file-pdf text-danger"></i>&nbsp;${title}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body p-0">
                            <div class="ratio ratio-16x9" style="min-height: 600px;">
                                <iframe src="${url}#toolbar=1&navpanes=0&scrollbar=1" allowfullscreen class="w-100 h-100">
                                </iframe>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <a href="${url}" target="_blank" class="btn btn-primary">
                                <i class="fa-regular fa-arrow-up-right"></i>&nbsp;Open in New Tab
                            </a>
                            <a href="${url.replace('/preview/', '/download/')}" class="btn btn-success">
                                <i class="fa-regular fa-download"></i>&nbsp;Download PDF
                            </a>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Add modal to body
        document.body.insertAdjacentHTML('beforeend', modal);
        
        // Show modal
        const modalElement = document.getElementById('pdfModal');
        const bsModal = new bootstrap.Modal(modalElement);
        bsModal.show();
        
        // Clean up on close
        modalElement.addEventListener('hidden.bs.modal', function() {
            modalElement.remove();
        });
    }

    // Image gallery with navigation
    function initializeGallery(answerId) {
        // Already handled by lightbox data attributes
    }

    // Lazy loading for images
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.classList.remove('lazy');
                    observer.unobserve(img);
                }
            });
        });
        
        document.querySelectorAll('img.lazy').forEach(img => {
            imageObserver.observe(img);
        });
    }
</script>
@endpush
@endonce