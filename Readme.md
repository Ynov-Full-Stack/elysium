To Start the app

```cmd
docker compose up -d
docker compose exec reminder php bin/console d:s:u --force
symfony server:start
```