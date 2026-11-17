@include('passaparola::errors.layout', [
    'status' => $status,
    'title' => __('passaparola::errors.'.$kind.'.title'),
    'message' => __('passaparola::errors.'.$kind.'.message'),
])
