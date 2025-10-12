@props(['form'])

@if($form->description)
<div class="alert alert-info alert-dismissible" role="alert">
    <div class="alert-icon">
        <i class="fa-regular fa-circle-info"></i>
    </div>
    <div>
        <h4 class="alert-heading">Form Description</h4>
        <div class="alert-description">
            {{ $form->description }}
        </div>
    </div>
</div>
@endif

<div class="alert alert-warning alert-dismissible" role="alert">
    <div class="alert-icon">
        <i class="fa-regular fa-triangle-exclamation"></i>
    </div>
    <div>
        <h4 class="alert-heading">Warning!</h4>
        <div class="alert-description">
            @if($form->requires_approval)
                This form requires approval. Your submission will be reviewed before completion.
            @else
                This form will be automatically approved upon submission.
            @endif
        </div>
    </div>
</div>