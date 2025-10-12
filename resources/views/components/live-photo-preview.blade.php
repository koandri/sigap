@props(['answer', 'field'])

@php
    $metadata = $answer->answer_metadata ?? [];
    $photos = $metadata['photos'] ?? [];
    $totalPhotos = $metadata['total_photos'] ?? 0;
    $isMultiple = $totalPhotos > 1;
@endphp

<div class="file-preview-container">
    @if($isMultiple && !empty($photos))
        <!-- Multiple photos -->
        <div class="row">
            @foreach($photos as $index => $photo)
                <div class="col-md-6 col-lg-6 mb-3">
                    <div class="card h-100">
                        @php
                            $extension = 'jpg'; // Live photos are always JPG
                            $isImage = true;
                        @endphp
                        
                        @if($isImage)
                            <!-- Image preview with Lightbox -->
                            <a href="{{ route('files.preview', [$answer->id, $index]) }}" data-lightbox="gallery-{{ $answer->id }}" data-title="Photo {{ $index + 1 }}">
                                <img src="{{ route('files.thumbnail', [$answer->id, $index]) }}?w=400&h=600&mode=fit&q=85" class="card-img-top" alt="Photo {{ $index + 1 }}" loading="lazy" style="height: 200px; object-fit: cover; cursor: zoom-in;">
                            </a>
                        @endif
                        
                        <div class="card-body p-2">
                            <p class="card-text small mb-1 text-truncate" title="Photo {{ $index + 1 }}">
                                <strong>Photo {{ $index + 1 }}</strong>
                            </p>
                            <p class="card-text small text-muted mb-2">
                                <i class="fa-regular fa-hard-drive"></i>&nbsp;{{ isset($photo['file_size']) ? number_format($photo['file_size'] / 1024, 1) . ' KB' : 'Unknown size' }}
                                <br>
                                <i class="fa-regular fa-file-code"></i>&nbsp;JPG
                            </p>
                            <div class="btn-group btn-group-sm w-100">
                                @if($isImage)
                                    <a href="{{ route('files.preview', [$answer->id, $index ?? null]) }}" data-lightbox="gallery-{{ $answer->id }}" data-title="Photo {{ $index + 1 }}" class="btn btn-outline-primary btn-sm">
                                        <i class="fa-regular fa-eye"></i>&nbsp;View
                                    </a>
                                @endif
                                
                                <a href="{{ route('files.download', [$answer->id, $index ?? null]) }}" class="btn btn-outline-success btn-sm">
                                    <i class="fa-regular fa-download"></i>&nbsp;Download
                                </a>
                                
                                @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) && $isImage)
                                    <a href="{{ route('files.download.original', [$answer->id, $index ?? null]) }}" class="btn btn-outline-warning btn-sm" title="Download without watermark">
                                        <i class="fa-regular fa-download"></i>&nbsp;Original
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        
        @if($totalPhotos > 0)
            <div class="mt-2">
                <small class="text-muted">
                    <i class="fa-regular fa-images"></i>&nbsp;{{ $totalPhotos }} photo(s) uploaded
                    | Click on images to view in gallery mode
                </small>
            </div>
        @endif
        
    @elseif(!empty($photos))
        <!-- Single photo -->
        @php
            $photo = $photos[0];
            $extension = 'jpg';
            $isImage = true;
        @endphp
        
        <div class="single-file-preview">
            @if($isImage)
                <!-- Image preview with Lightbox -->
                <div class="mb-3 text-center">
                    <a href="{{ route('files.preview', $answer->id) }}" data-lightbox="single-{{ $answer->id }}" data-title="Photo">
                        <img src="{{ route('files.thumbnail', $answer->id) }}?w=600&h=800&mode=fit&q=85" class="img-fluid rounded shadow-sm" alt="Photo" style="max-height: 400px; cursor: zoom-in;" loading="lazy">
                    </a>
                    <small class="text-muted d-block mt-2">
                        <i class="fa-regular fa-magnifying-glass-plus"></i>&nbsp;Click image to enlarge
                    </small>
                </div>
            @endif
            
            <!-- File info and actions -->
            <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded">
                <div>
                    @if($isImage)
                        <i class="fa-regular fa-file-image text-primary me-2" style="font-size: 1.5rem;"></i>
                    @else
                        <i class="fa-regular fa-file text-secondary me-2" style="font-size: 1.5rem;"></i>
                    @endif
                    
                    <span>
                        <strong>Photo</strong>
                        <br>
                        <small class="text-muted">
                            Size: {{ isset($photo['file_size']) ? number_format($photo['file_size'] / 1024, 1) . ' KB' : 'Unknown' }}
                            | Type: JPG
                        </small>
                    </span>
                </div>
                <div class="btn-group">
                    @if($isImage)
                        <a href="{{ route('files.preview', [$answer->id, $index ?? null]) }}" data-lightbox="single-{{ $answer->id }}" data-title="Photo" class="btn btn-outline-primary btn-sm">
                            <i class="fa-regular fa-eye"></i>&nbsp;View
                        </a>
                    @endif
                    
                    <a href="{{ route('files.download', [$answer->id, $index ?? null]) }}" class="btn btn-outline-success btn-sm">
                        <i class="fa-regular fa-download"></i>&nbsp;Download
                    </a>
                    
                    @if(auth()->user()->hasAnyRole(['Super Admin', 'Owner']) && $isImage)
                        <a href="{{ route('files.download.original', [$answer->id, $index ?? null]) }}" class="btn btn-outline-warning btn-sm" title="Download without watermark">
                            <i class="fa-regular fa-download"></i>&nbsp;Original
                        </a>
                    @endif
                </div>
            </div>
        </div>
        
    @else
        <!-- No photos -->
        <div class="text-center py-4">
            <i class="fa-regular fa-file-image" style="font-size: 3rem; color: #6c757d;"></i>
            <p class="text-muted mt-2">No photos uploaded</p>
        </div>
    @endif
</div>


