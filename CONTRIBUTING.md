# Contributing

Issues and pull requests are welcome. Keep a change small and covered by tests - the suite runs at 100 % line coverage.

## The workbench

The workbench has a Livewire form and a plain form against a real mosparo.

With [lerd](https://github.com/lerd-dev/lerd): `.lerd.yaml` asks for the mosparo service, which creates a project for the site. Then open `https://laravel-mosparo.test`.

With Docker:

```bash
docker compose run --rm composer install
docker compose up serve mosparo
```

Open `http://localhost:8000`. mosparo's own admin is at `http://localhost:8088` (`admin@example.com` / `password`).

Run the tests and the formatter:

```bash
composer test
composer lint
```

Format before committing - CI checks it.
