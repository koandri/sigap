@if ($paginator->hasPages())
                                    <div class="row g-2 justify-content-center justify-content-sm-between">
                                        <div class="col-auto d-flex align-items-center">
                                            <p class="m-0 text-secondary">
                                                {!! __('Showing') !!}
                                                <strong>{{ $paginator->firstItem() }}</strong>
                                                {!! __('to') !!}
                                                <span class="<strong>">{{ $paginator->lastItem() }}</strong>
                                                {!! __('of') !!}
                                                <span class="<strong>">{{ $paginator->total() }}</strong>
                                                {!! __('results') !!}
                                            </p>
                                        </div>
                                        <div class="col-auto">
                                            <ul class="pagination m-0 ms-auto">
                                                {{-- Previous Page Link --}}
                                                @if ($paginator->onFirstPage())
                                                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                                                    <span class="page-link" aria-hidden="true"><i class="far fa-chevron-left"></i>&nbsp;</span>
                                                </li>
                                                @else
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')"><i class="far fa-chevron-left"></i>&nbsp;</a>
                                                </li>
                                                @endif

                                                {{-- Pagination Elements --}}
                                                @foreach ($elements as $element)
                                                {{-- "Three Dots" Separator --}}
                                                @if (is_string($element))
                                                <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                                                @endif

                                                {{-- Array Of Links --}}
                                                @if (is_array($element))
                                                @foreach ($element as $page => $url)
                                                @if ($page == $paginator->currentPage())
                                                <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                                                @else
                                                <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                                @endif
                                                @endforeach
                                                @endif
                                                @endforeach                                        

                                                {{-- Next Page Link --}}
                                                @if ($paginator->hasMorePages())
                                                <li class="page-item">
                                                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')"><i class="far fa-chevron-right"></i>&nbsp;</a>
                                                </li>
                                                @else
                                                <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                                                    <span class="page-link" aria-hidden="true"><i class="far fa-chevron-right"></i>&nbsp;</span>
                                                </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div> 
@endif