@props(['submission'])

<div class="col-12">
    <div class="card">
        <div class="card-body border-bottom py-3">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="40%">Form Name:</th>
                            <td>{{ $submission->formVersion->form->name }}</td>
                        </tr>
                        <tr>
                            <th>Form Number:</th>
                            <td>{{ $submission->formVersion->form->form_no }}</td>
                        </tr>
                        <tr>
                            <th>Version:</th>
                            <td>v{{ $submission->formVersion->version_number }}</td>
                        </tr>
                        <tr>
                            <th>Submitted By:</th>
                            <td>{{ $submission->submitter->name }}</td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <th width="40%">Status:</th>
                            <td>
                                <x-status-badge :status="$submission->status" />
                            </td>
                        </tr>
                        <tr>
                            <th>Submitted At:</th>
                            <td>{{ formatDate($submission->submitted_at, 'd M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Completed At:</th>
                            <td>{{ formatDate($submission->completed_at, 'd M Y H:i') }}</td>
                        </tr>
                        <tr>
                            <th>Submission Code:</th>
                            <td>
                                <code class="submission-code" data-code="{{ $submission->submission_code }}">
                                    {{ $submission->submission_code }}
                                </code>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>