@props(['status'])

@php
    $statusConfig = [
        'draft' => ['class' => 'badge-outline text-secondary', 'icon' => 'fa-edit'],
        'submitted' => ['class' => 'badge-outline text-info', 'icon' => 'fa-paper-plane'],
        'under_review' => ['class' => 'badge-outline text-warning', 'icon' => 'fa-eye'],
        'approved' => ['class' => 'badge-outline text-success', 'icon' => 'fa-check-circle'],
        'rejected' => ['class' => 'badge-outline text-danger', 'icon' => 'fa-times-circle'],
        'cancelled' => ['class' => 'badge-outline text-dark', 'icon' => 'fa-ban'],
    ];
    
    $config = $statusConfig[$status] ?? $statusConfig['draft'];
@endphp

<span class="badge {{ $config['class'] }}">
    <i class="far {{ $config['icon'] }}"></i>
    {{ ucfirst(str_replace('_', ' ', $status)) }}
</span>