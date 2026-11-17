@include('passaparola::errors.layout', [
    'status' => 404,
    'title' => __('passaparola::errors.not_found.title'),
    'message' => __('passaparola::errors.not_found.message'),
])
