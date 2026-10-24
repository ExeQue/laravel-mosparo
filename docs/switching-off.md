# Disabling mosparo

```dotenv
MOSPARO_ENABLED=false
```

When disabled:

- every check passes;
- `@mosparoScript`, `@mosparo` and `<x-mosparo />` render nothing;
- a Livewire form is still validated by `verify()`.

Use it for local work without a mosparo instance, for tests and for screenshots.

When enabled, a project that misses its host, uuid or one of its keys throws an `IncompleteProject` exception. A check through `passes()`, `ValidMosparo` or `verify()` reports the exception and fails, so forms are never let through by an incomplete configuration.
