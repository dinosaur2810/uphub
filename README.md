# UpHub

A premium, open-source platform designed to bridge the gap between job seekers, recruiters, and community support services. Built with a human-first philosophy, it combines robust backend functionality with a cinematic, modern Indigo design system.

## Stack

- PHP 8.2+ with Apache
- MySQL / MariaDB
- Symfony Mailer (Composer)
- Docker & Jenkins CI

## Local setup (XAMPP)

See [SETUP_GUIDE.md](SETUP_GUIDE.md). Copy `config/config.example.php` to `config/config.local.php` and set your database, maps, and mail credentials.

## Docker

```bash
docker compose up -d --build
```

App: http://localhost:8081  
Database: `uphub` / user `uphub` / password `uphub` (see `docker-compose.yml`)

Stop:

```bash
docker compose down
```

## Jenkins

Pipeline job **uphub** clones this repo and runs `Jenkinsfile`:

1. Checkout from GitHub
2. `docker compose build` (with CI port overlay)
3. Start stack, health-check `index.php`, tear down

Repository: https://github.com/dinosaur2810/uphub

## License

Open source — see project documentation for usage terms.
