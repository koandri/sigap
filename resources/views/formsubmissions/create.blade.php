<x-layouts.form-submission 
    :form="$form"
    :version="$version"
    :fields="$fields"
    :prefillData="$prefillData ?? []"
    :formAction="route('formsubmissions.store', $form)"
    formMethod="POST" />