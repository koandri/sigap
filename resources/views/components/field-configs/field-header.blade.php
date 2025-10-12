@props(['form', 'version', 'isEdit' => false, 'field' => null])

<!-- BEGIN PAGE HEADER -->
<div class="page-header d-print-none" aria-label="Page header">
    <div class="container-xl">
        <div class="row g-2 align-items-center">
            <div class="col">
                <h2 class="page-title">
                    {{ $isEdit ? 'Edit Form Field' : 'Add Form Field' }}
                </h2>
            </div>
        </div>
    </div>
</div>
<!-- END PAGE HEADER -->