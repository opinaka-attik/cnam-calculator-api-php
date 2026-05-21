Found the root cause. The `docker-compose.yml` mounts the entire host directory over `/app`:

```yaml
volumes:
  - ./:/app
```

This **overwrites** the `vendor/` folder that was created by `RUN composer install` in the Docker build — because there's no `vendor/` on your host. The image builds fine, but the volume mount hides it at runtime.

**Quick fix** — run composer inside the running container:

```bash
docker compose exec api composer install
docker compose exec api ./vendor/bin/phpunit --testdox
```

**Permanent fix** — add a named volume for `vendor/` so it's not overwritten. Update `docker-compose.yml`:

Edited docker-compose.yml

Now rebuild and restart:

```bash
docker compose down
docker compose up --build -d
docker compose exec api ./vendor/bin/phpunit --testdox
```

The named volume `vendor:` will be populated from the image layer (where `composer install` ran), and the host bind mount won't overwrite it.