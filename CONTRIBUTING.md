# Contributing

Issues and pull requests are welcome. Keep a change small and covered by tests: CI requires 100 % line coverage and checks the formatting.

## The workbench

The workbench has four pages of forms against a real mosparo: a Livewire sign-in, a plain form, a visible checkbox next to a box forced invisible, and a form mosparo rejects.

### With lerd

[lerd](https://github.com/lerd-dev/lerd) serves the workbench at `https://laravel-mosparo.test`, and `.lerd.yaml` asks for its mosparo service.

```bash
composer install
```

The mosparo service creates a project per site. The workbench also uses a second, visible project. Create both once:

```bash
podman exec lerd-mosparo php /lerd/site-init.php laravel_mosparo
podman exec lerd-mosparo php /lerd/site-init.php laravel_mosparo_visible
podman cp docker/mosparo/visible.php lerd-mosparo:/tmp/visible.php
podman exec lerd-mosparo php /tmp/visible.php lerd-laravel_mosparo_visible
podman exec lerd-mosparo chown -R www-data /mosparo-data
```

### With Docker

```bash
docker compose run --rm composer install
docker compose up serve mosparo
```

Open `http://localhost:8000`. mosparo itself is at `http://localhost:8088` (`admin@example.com` / `password`), with both projects created on start.

## Checks

```bash
composer test           # the suite
composer test:coverage  # the suite, failing below 100 % coverage
composer lint           # format the code
composer lint:test      # check the formatting, as CI does
```

With Docker, run them through the composer service: `docker compose run --rm composer test`.

The documentation is built with VitePress:

```bash
npm install
npm run docs:dev
```
