# Translations

The package has one message, `mosparo::messages.failed`: *"The spam check failed. Please try again."* It is shown when a check fails and when the box's tokens are missing, in the application's locale.

It ships in Bulgarian, Catalan, Croatian, Czech, Danish, Dutch, English, Estonian, Finnish, French, German, Greek, Hungarian, Icelandic, Irish, Italian, Latvian, Lithuanian, Maltese, Norwegian Bokmål, Polish, Portuguese, Romanian, Serbian, Slovak, Slovenian, Spanish, Swedish and Ukrainian.

To change the wording, publish the translations to `lang/vendor/mosparo`:

```bash
php artisan vendor:publish --tag=mosparo-lang
```

The texts inside the box come from mosparo, in the language of the page's `<html lang>`, or the `language` [option](box.md#options).
