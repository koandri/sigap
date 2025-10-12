@props(['form', 'version', 'submission' => null])

<!-- BEGIN PAGE HEADER -->
<div class="page-header d-print-none" aria-label="Page header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">{{ $form->name }}</h2>
                <small class="text-muted">
                    Form No: {{ $form->form_no }} | Version: {{ $version->version_number }}
                    @if($submission)
                        | Submission Code: {{ $submission->submission_code }}
                    @endif
                </small>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE HEADER -->