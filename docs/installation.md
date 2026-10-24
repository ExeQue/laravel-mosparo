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

## Laravel Boost

The package ships a [Laravel Boost](https://github.com/laravel/boost) skill, `laravel-mosparo`, which tells a coding agent how to protect, change and test a form with it. Boost offers it when you run `php artisan boost:install` or `php artisan boost:update`.

Next: [put the box in a form](box.md).
