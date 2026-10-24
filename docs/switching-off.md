<!-- Part of the laravel-mosparo documentation - see ../README.md -->

# Switching it off

```dotenv
MOSPARO_ENABLED=false
```

Switched off, every check passes, and `@mosparoScript` and `@mosparo` render nothing. This is for local work without a mosparo instance and for screenshots. A project without a host, uuid or one of its keys counts as switched off too.
