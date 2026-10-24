<!-- Part of the laravel-mosparo documentation - see ../README.md -->

# Translations

The one message, *"The spam check failed. Please try again."*, ships in Bulgarian, Catalan, Croatian, Czech, Danish, Dutch, English, Estonian, Finnish, French, German, Greek, Hungarian, Icelandic, Irish, Italian, Latvian, Lithuanian, Maltese, Norwegian Bokmål, Polish, Portuguese, Romanian, Serbian, Slovak, Slovenian, Spanish, Swedish and Ukrainian. It follows the application locale. To change the wording:

```bash
php artisan vendor:publish --tag=mosparo-lang
```

The texts inside the box come from mosparo, in the language of the page's `<html lang>`, or the `language` option.
