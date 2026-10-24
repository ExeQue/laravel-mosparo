<!-- Part of the laravel-mosparo documentation - see ../README.md -->

# Configuration

| Key | Env | Default | |
| --- | --- | --- | --- |
| `enabled` | `MOSPARO_ENABLED` | `true` | Switch for every project. |
| `default` | `MOSPARO_PROJECT` | `default` | The project used when none is named. |
| `host` | `MOSPARO_HOST` | | The mosparo installation, shared by every project. |
| `projects.*.host` | | `host` | A project's own installation, when it has one. |
| `projects.*.uuid` | `MOSPARO_UUID` | | The project's uuid. |
| `projects.*.public_key` | `MOSPARO_PUBLIC_KEY` | | |
| `projects.*.private_key` | `MOSPARO_PRIVATE_KEY` | | Only ever used on the server. |
| `projects.*.client_arguments` | `MOSPARO_TIMEOUT` | `['timeout' => 10]` | Guzzle options for the verification request, such as `'verify' => false` against a self-signed local instance. |
