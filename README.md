# UpHub

A premium, open-source platform for job seekers, recruiters, and community support services — PHP, MySQL, Docker, and Jenkins CI.

**GitHub:** https://github.com/dinosaur2810/uphub  

---

## Documentation (step-by-step)

| Guide | What it covers |
|-------|----------------|
| **[docs/SETUP-JENKINS-AND-DOCKER.md](docs/SETUP-JENKINS-AND-DOCKER.md)** | **Start here** — Docker on `localhost:8081`, create or recreate Jenkins pipeline `uphub` |
| [SETUP_GUIDE.md](SETUP_GUIDE.md) | XAMPP, Google Maps, Gmail SMTP, database import |
| [docs/JENKINS-GITHUB-WEBHOOK.md](docs/JENKINS-GITHUB-WEBHOOK.md) | GitHub webhooks and Jenkins token types |

---

## Quick start — Docker

```powershell
cd c:\xampp\htdocs\UpHub
docker compose up -d --build
```

Open **http://localhost:8081/UpHub/** (same `/UpHub` path as XAMPP)

Stop:

```powershell
docker compose down
```

Redo from scratch:

```powershell
docker compose down -v
docker compose up -d --build
```

Full details → [docs/SETUP-JENKINS-AND-DOCKER.md](docs/SETUP-JENKINS-AND-DOCKER.md#part-1--docker-run-uphub-on-localhost8081)

---

## Quick start — Jenkins pipeline

1. Open http://localhost:8080  
2. **New Item** → name `uphub` → **Pipeline**  
3. **Pipeline script from SCM** → Git → `https://github.com/dinosaur2810/uphub.git` → branch `*/main` → script `Jenkinsfile`  
4. **Build Now**

If the job was deleted, repeat the steps above (same job name and settings).

Full details → [docs/SETUP-JENKINS-AND-DOCKER.md](docs/SETUP-JENKINS-AND-DOCKER.md#part-2--jenkins-new-pipeline-or-recreate-after-delete)

---

## Deploy (push to GitHub + Jenkins build)

```powershell
copy .env.jenkins.example .env.jenkins   # once: add JENKINS_USER + JENKINS_API_TOKEN
.\scripts\deploy-and-build.ps1 -Message "your commit message"
```

---

## Stack

- PHP 8.2 + Apache  
- MariaDB / MySQL  
- Symfony Mailer (Composer)  
- Docker & Jenkins (`Jenkinsfile`)
