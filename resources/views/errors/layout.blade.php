{{-- Pagina d'errore autonoma: non dipende dal tema, cosi' un errore del layout non diventa un 500. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} — Passaparola</title>
    <style>
        body { font-family: system-ui, sans-serif; margin: 0; min-height: 100vh; display: grid; place-items: center; background: #f8fafc; color: #0f172a; }
        main { max-width: 32rem; padding: 2rem; text-align: center; }
        h1 { font-size: 1.75rem; margin: 0 0 .75rem; }
        p { line-height: 1.6; color: #334155; }
        a { color: #0f766e; font-weight: 600; }
    </style>
</head>
<body>
<main>
    <p aria-hidden="true">{{ $status }}</p>
    <h1>{{ $title }}</h1>
    <p>{{ $message }}</p>
    <p><a href="{{ url('/'.app()->getLocale()) }}">{{ __('passaparola::errors.home') }}</a></p>
</main>
</body>
</html>
