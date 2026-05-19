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

## Deploy + auto-rebuild (local → GitHub → Jenkins)

1. Copy `.env.jenkins.example` to `.env.jenkins` and set `JENKINS_API_TOKEN` (user must be `tagayanfinal`, not the token label).
2. From the project folder:

```powershell
.\scripts\deploy-and-build.ps1 -Message "your commit message"
```

This pushes to GitHub and queues the Jenkins **uphub** job.

Trigger build only (no git push):

```powershell
.\scripts\trigger-jenkins-build.ps1
```

**Jenkins job must be configured:** Git URL `https://github.com/dinosaur2810/uphub.git`, branch `*/main`.

## Jenkins

Pipeline job **uphub** clones this repo and runs `Jenkinsfile`:

1. Checkout from GitHub
2. `docker compose build` (with CI port overlay)
3. Start stack, health-check `index.php`, tear down

Repository: https://github.com/dinosaur2810/uphub

## License

Open source — see project documentation for usage terms.
