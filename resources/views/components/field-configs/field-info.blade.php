@props(['form', 'version', 'isEdit' => false, 'field' => null, 'hasSubmissions' => false])

<div class="alert alert-info alert-dismissible" role="alert">
    <div class="alert-icon">
        <i class="fa-regular fa-circle-info"></i>
    </div>
    <div>
        <h4 class="alert-heading">Info!</h4>
        <div class="alert-description">
            @if($isEdit)
                Editing field in: <strong>{{ $form->name }}</strong> - v{{ $version->version_number }}
            @else
                Adding field to: <strong>{{ $form->name }}</strong> - Version {{ $version->version_number }}
            @endif
        </div>
    </div>
</div>

@if($hasSubmissions)
<div class="alert alert-warning alert-dismissible" role="alert">
    <div class="alert-icon">
        <i class="fa-sharp fa-solid fa-triangle-exclamation"></i>
    </div>
    <div>
        <h4 class="alert-heading">Warning!</h4>
        <div class="alert-description">
            <strong>Limited Editing:</strong> This version has submissions. You can only edit labels and help text.
        </div>
    </div>
</div>
@endif