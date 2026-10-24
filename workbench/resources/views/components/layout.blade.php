@props(['title' => null])
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ? "{$title} - " : '' }}laravel-mosparo</title>
    @mosparoScript
    <style>
        :root {
            --accent: #206bc4;
            --accent-dark: #1a56a8;
            --text: #1d2433;
            --muted: #5b6475;
            --line: #e3e7ee;
            --surface: #ffffff;
            --background: #f4f6fa;
            --success: #0f7b4b;
            --success-soft: #e7f6ee;
            --danger: #b42318;
            --danger-soft: #fdeceb;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font: 16px/1.55 ui-sans-serif, system-ui, -apple-system, "Segoe UI", sans-serif;
            color: var(--text);
            background: var(--background);
        }

        header {
            background: var(--surface);
            border-bottom: 1px solid var(--line);
        }

        .bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1rem 2rem;
            max-width: 46rem;
            margin: 0 auto;
            padding: 1rem;
        }

        .brand {
            font-weight: 700;
            color: var(--text);
            text-decoration: none;
        }

        .brand span { color: var(--accent); }

        nav {
            display: flex;
            flex-wrap: wrap;
            gap: .25rem;
        }

        nav a {
            padding: .35rem .75rem;
            border-radius: 999px;
            color: var(--muted);
            text-decoration: none;
            font-size: .925rem;
        }

        nav a:hover { color: var(--text); background: var(--background); }
        nav a[aria-current="page"] { color: var(--accent-dark); background: #e8f0fb; font-weight: 600; }

        main {
            max-width: 46rem;
            margin: 2.5rem auto;
            padding: 0 1rem;
        }

        h1 { font-size: 1.75rem; line-height: 1.2; margin: 0 0 .5rem; }
        .lead { color: var(--muted); margin: 0 0 1.5rem; }
        code { font-size: .875em; background: #eef1f6; padding: .1em .35em; border-radius: 4px; }

        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 1px 2px rgba(16, 24, 40, .04);
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(13rem, 1fr));
            gap: 1rem;
        }

        .cards a.card {
            color: inherit;
            text-decoration: none;
            transition: border-color .15s, transform .15s;
        }

        .cards a.card:hover { border-color: var(--accent); transform: translateY(-2px); }
        .cards h2 { font-size: 1.05rem; margin: 0 0 .35rem; color: var(--accent-dark); }
        .cards p { margin: 0; color: var(--muted); font-size: .925rem; }

        form { display: grid; gap: 1rem; }

        label { display: grid; gap: .35rem; font-weight: 600; font-size: .925rem; }

        input:not([type="checkbox"]), textarea {
            width: 100%;
            padding: .6rem .75rem;
            font: inherit;
            font-weight: 400;
            color: var(--text);
            border: 1px solid #cdd3de;
            border-radius: 8px;
            background: var(--surface);
        }

        textarea { min-height: 6rem; resize: vertical; }

        input:focus, textarea:focus {
            outline: 3px solid rgba(32, 107, 196, .2);
            border-color: var(--accent);
        }

        .check {
            display: flex;
            align-items: center;
            gap: .5rem;
            font-weight: 400;
        }

        .check input { width: 1.1rem; height: 1.1rem; accent-color: var(--accent); }

        button {
            justify-self: start;
            padding: .65rem 1.25rem;
            font: inherit;
            font-weight: 600;
            color: #fff;
            background: var(--accent);
            border: 0;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover { background: var(--accent-dark); }
        button:disabled { opacity: .6; cursor: progress; }

        .notice {
            margin: 0;
            padding: .75rem 1rem;
            border-radius: 8px;
            font-size: .95rem;
        }

        .notice.success { color: var(--success); background: var(--success-soft); }
        .notice.error { color: var(--danger); background: var(--danger-soft); }

        .hint {
            margin: 0 0 .75rem;
            color: var(--muted);
            font-size: .9rem;
        }

        h2.section { font-size: 1.15rem; margin: 2rem 0 .25rem; }
    </style>
</head>
<body>
    <header>
        <div class="bar">
            <a class="brand" href="/">laravel-<span>mosparo</span></a>
            <nav>
                <a href="/livewire" wire:navigate @if(request()->is('livewire')) aria-current="page" @endif>Livewire</a>
                <a href="/form" @if(request()->is('form')) aria-current="page" @endif>Plain form</a>
                <a href="/visible" @if(request()->is('visible')) aria-current="page" @endif>Visible</a>
                <a href="/rejected" wire:navigate @if(request()->is('rejected')) aria-current="page" @endif>Rejected</a>
            </nav>
        </div>
    </header>
    <main>
        {{ $slot }}
    </main>
</body>
</html>
