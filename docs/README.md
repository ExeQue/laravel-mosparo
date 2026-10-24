<!-- Part of the laravel-mosparo documentation - see ../README.md -->

# Documentation

- [Installation](installation.md) - Requirements, and the four keys from mosparo
- [The box](box.md) - `@mosparoScript`, `@mosparo` and `<x-mosparo />`
- [Plain forms](plain-forms.md) - The `ValidMosparo` validation rule
- [Livewire](livewire.md) - The `InteractsWithMosparo` trait and `MosparoForm`
- [The facade](facade.md) - Talking to mosparo from the backend
- [Several projects](projects.md) - More than one mosparo project in one application
- [Switching it off](switching-off.md) - For local work and screenshots
- [Translations](translations.md) - The error message in 29 languages
- [Testing your application](testing.md) - Forms with mosparo in your test suite
- [Configuration](configuration.md) - Every option in `config/mosparo.php`
- [How it works](how-it-works.md) - Tokens, the check and the reset

## Where things are

| Looking for | Page |
|---|---|
| `MOSPARO_HOST`, `MOSPARO_UUID`, the keys | [Installation](installation.md#installing) |
| `@mosparoScript` | [The box](box.md) |
| `@mosparo`, `<x-mosparo />` and its attributes | [The box](box.md) |
| `new ValidMosparo` | [Plain forms](plain-forms.md) |
| `InteractsWithMosparo`, `$this->mosparo->verify()` | [Livewire](livewire.md) |
| Inputs need a `name` | [Livewire](livewire.md#which-fields-mosparo-sees) |
| `Mosparo::passes()`, `verify()`, `client()` | [The facade](facade.md) |
| `Mosparo::project('newsletter')` | [Several projects](projects.md) |
| `MOSPARO_ENABLED` | [Switching it off](switching-off.md) |
| `mosparo-reset` | [How it works](how-it-works.md) |
