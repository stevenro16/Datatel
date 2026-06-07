<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32.png">
        <link rel="apple-touch-icon" sizes="512x512" href="/favicon-512.png">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>{{ config('app.name', 'DataTel') }}</title>
        <style>
            :root { --primary: #1A3C5E; --accent: #2E86C1; }
            * { box-sizing: border-box; margin: 0; padding: 0; }
            body { font-family: system-ui, sans-serif; background: #F8F9FA; color: #333; }
            .screen { min-height: 100vh; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 2rem; }
            .logo-wrap { margin-bottom: 1.5rem; }
            .logo-wrap img { height: 120px; }
            .card { width: 100%; max-width: 420px; background: #fff; border-radius: 8px;
                    padding: 2rem; box-shadow: 0 4px 16px rgba(0,0,0,.1); }
            label { display: block; font-size: .85rem; font-weight: 600; color: #444; margin-bottom: .35rem; }
            input[type=email], input[type=password], input[type=text] {
                width: 100%; padding: .6rem .85rem; border: 1px solid #ccc;
                border-radius: 5px; font-size: .95rem; margin-bottom: 1rem; }
            input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(46,134,193,.15); }
            .form-group { margin-bottom: 1rem; }
            .btn-primary { width: 100%; padding: .7rem; background: var(--primary); color: #fff;
                           border: none; border-radius: 5px; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: .5rem; }
            .btn-primary:hover { background: var(--accent); }
            .form-footer { display: flex; justify-content: space-between; align-items: center; margin-top: 1rem; font-size: .85rem; }
            .form-footer a { color: var(--accent); text-decoration: none; }
            .form-footer a:hover { text-decoration: underline; }
            .error-msg { color: #C0392B; font-size: .82rem; margin-top: -.6rem; margin-bottom: .75rem; }
            .status-msg { background: #d1fae5; color: #065f46; padding: .6rem .85rem; border-radius: 5px; margin-bottom: 1rem; font-size: .88rem; }
            .remember { display: flex; align-items: center; gap: .5rem; font-size: .88rem; color: #555; margin-bottom: .5rem; }
            button[type=submit] { display: inline-flex; align-items: center; padding: .6rem 1.25rem;
                background: var(--primary); color: #fff; border: none; border-radius: 5px;
                font-size: .9rem; font-weight: 600; cursor: pointer; }
            button[type=submit]:hover { background: var(--accent); }
            .error-list { color: #C0392B; font-size: .82rem; list-style: none; margin-top: -.5rem; margin-bottom: .75rem; }
        </style>
    </head>
    <body>
        <div class="screen">
            <div class="logo-wrap">
                <a href="/"><img src="{{ route('site.logo') }}" alt="{{ config('app.name') }}"></a>
            </div>
            <div class="card">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
