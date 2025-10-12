<x-layouts.form-submission 
    :form="$form"
    :version="$version"
    :submission="$submission"
    :fields="$fields"
    :prefillData="[]"
    :formAction="route('formsubmissions.update', $submission)"
    formMethod="POST" />