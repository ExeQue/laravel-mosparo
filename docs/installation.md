<!-- Part of the laravel-mosparo documentation - see ../README.md -->

# Installation

## Requirements

- PHP 8.4+
- Laravel 13
- Livewire 4, for the Livewire parts
- A mosparo installation, 1.5 or newer

## Installing

```bash
composer require exeque/laravel-mosparo
```

The service provider and the `Mosparo` facade register themselves.

Create a project in mosparo and put its details in `.env`:

```dotenv
MOSPARO_HOST=https://mosparo.example.com
MOSPARO_UUID=your-project-uuid
MOSPARO_PUBLIC_KEY=your-public-key
MOSPARO_PRIVATE_KEY=your-private-key
```

That is all the configuration a single project needs.

Next: [put the box on a page](box.md).
