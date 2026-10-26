# Configuration

Publish the configuration to change more than the `.env` keys cover:

```bash
php artisan vendor:publish --tag=mosparo-config
```

| Key | Env | Default | |
| --- | --- | --- | --- |
| `enabled` | `MOSPARO_ENABLED` | `true` | Disabled, every check passes and nothing is rendered. See [Disabling mosparo](switching-off.md). |
| `host` | `MOSPARO_HOST` | | The mosparo installation, for every project: its origin, such as `https://mosparo.example.com`, without a path. |
| `client_arguments` | `MOSPARO_TIMEOUT` | `['timeout' => 10]` | Guzzle options for every request to mosparo, for every project. Add `'verify' => false` for a local mosparo with a self-signed certificate. |
| `cache.enabled` | `MOSPARO_CACHE` | `false` | Whether what is read from mosparo is cached. See [Caching](caching.md). |
| `cache.store` | `MOSPARO_CACHE_STORE` | the default store | |
| `cache.ttl` | `MOSPARO_CACHE_TTL` | `300` | Seconds an entry is kept. |
| `default` | `MOSPARO_PROJECT` | `default` | The project used when none is named. |
| `token_sets` | | `[]` | Token names by a name of your own, `['api' => ['mosparo_submit_token', 'mosparo_validation_token']]`, for `ValidMosparo::make()->for('api')`. See [Plain forms](plain-forms.md#json-and-graphql-inputs). |
| `projects.default.uuid` | `MOSPARO_UUID` | | The default project's uuid. |
| `projects.default.public_key` | `MOSPARO_PUBLIC_KEY` | | |
| `projects.default.private_key` | `MOSPARO_PRIVATE_KEY` | | Only ever used on the server. |

Every project, the default one included, can also set:

| Key | |
| --- | --- |
| `projects.*.host` | Its own mosparo installation, instead of `host`. |
| `projects.*.client_arguments` | Guzzle options that replace those in `client_arguments`, option by option. |
| `projects.*.cache` | Cache settings that replace those in `cache`, setting by setting. |

See [Several projects](projects.md).
