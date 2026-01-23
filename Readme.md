To Start the app

```cmd
docker compose up -d
docker compose exec elysium_reminder php bin/console d:s:u --force
symfony server:start
```