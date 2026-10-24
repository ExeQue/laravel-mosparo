# Configuration

Publish the configuration to change more than the `.env` keys cover:

```bash
php artisan vendor:publish --tag=mosparo-config
```

| Key | Env | Default | |
| --- | --- | --- | --- |
| `enabled` | `MOSPARO_ENABLED` | `true` | Disabled, every check passes and nothing is rendered. See [Disabling mosparo](switching-off.md). |
| `host` | `MOSPARO_HOST` | | The mosparo installation, for every project. |
| `client_arguments` | `MOSPARO_TIMEOUT` | `['timeout' => 10]` | Guzzle options for the verification request, for every project. Add `'verify' => false` for a local mosparo with a self-signed certificate. |
| `default` | `MOSPARO_PROJECT` | `default` | The project used when none is named. |
| `projects.default.uuid` | `MOSPARO_UUID` | | The default project's uuid. |
| `projects.default.public_key` | `MOSPARO_PUBLIC_KEY` | | |
| `projects.default.private_key` | `MOSPARO_PRIVATE_KEY` | | Only ever used on the server. |

Every project, the default one included, can also set:

| Key | |
| --- | --- |
| `projects.*.host` | Its own mosparo installation, instead of `host`. |
| `projects.*.client_arguments` | Guzzle options merged over `client_arguments`. |

See [Several projects](projects.md).
