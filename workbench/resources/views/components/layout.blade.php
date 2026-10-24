<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>laravel-mosparo</title>
    @mosparoScript
    <style>
        body { font-family: system-ui, sans-serif; max-width: 32rem; margin: 3rem auto; padding: 0 1rem; }
        label { display: block; margin-bottom: 1rem; }
        input { display: block; width: 100%; padding: .5rem; }
        .error { color: #b91c1c; }
        .result { font-weight: 600; }
        nav a { margin-right: 1rem; }
    </style>
</head>
<body>
    <nav><a href="/livewire" wire:navigate>Livewire</a> <a href="/form">Plain form</a></nav>
    {{ $slot }}
</body>
</html>
