# Installation

## Requirements

- PHP 8.4 or newer
- Laravel 13
- Livewire 4, for the Livewire parts
- A mosparo installation, version 1.5 or newer

## Installing

```bash
composer require exeque/laravel-mosparo
```

The service provider and the `Mosparo` facade register themselves.

Create a project in mosparo, and put its details in `.env`:

```dotenv
MOSPARO_HOST=https://mosparo.example.com
MOSPARO_UUID=your-project-uuid
MOSPARO_PUBLIC_KEY=your-public-key
MOSPARO_PRIVATE_KEY=your-private-key
```

That is all a single project needs. With mosparo enabled - the default - a missing value throws an `IncompleteProject` exception rather than letting forms through, so a typo in `.env` shows up at once. To work without mosparo, [disable it](switching-off.md).

Next: [put the box in a form](box.md).
