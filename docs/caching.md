# Caching

What is read from mosparo - [statistics and rule packages](data.md) - can be cached. Checks of a submission are never cached.

## Switching it on

```dotenv
MOSPARO_CACHE=true
MOSPARO_CACHE_TTL=300
MOSPARO_CACHE_STORE=redis
```

Leave out `MOSPARO_CACHE_STORE` to use the application's default cache store.

## Per project

A project's own `cache` settings are merged over the shared ones:

```php
'projects' => [
    'newsletter' => [
        // ...
        'cache' => ['enabled' => true, 'ttl' => 60],
    ],
],
```

## Per call

```php
Mosparo::withoutCache()->statistics();             // straight from mosparo
Mosparo::withCache(ttl: 60)->statistics();         // cached for a minute, whatever the configuration says
Mosparo::project('newsletter')->withoutCache()->rulePackageRules(3);
```

## Clearing

```php
Mosparo::flushCache();              // every project
Mosparo::flushCache('newsletter');  // one project
```

```bash
php artisan mosparo:clear-cache
php artisan mosparo:clear-cache newsletter
```

Clearing touches nothing but mosparo's own entries, on every cache store, also those without tags. Importing or updating a rule package clears that project's entries by itself.
