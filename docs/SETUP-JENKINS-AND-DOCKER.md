# UpHub — Step-by-step: Docker & Jenkins

Complete guide to run UpHub in **Docker** on your PC and set up (or recreate) the **Jenkins** pipeline.

**Repository:** https://github.com/dinosaur2810/uphub  
**Jenkins job name:** `uphub`  
**Docker app URL:** http://localhost:8081  
**Jenkins URL:** http://localhost:8080  

---

## Part 1 — Docker (run UpHub on localhost:8081)

### Step 1: Install Docker Desktop

1. Download [Docker Desktop for Windows](https://www.docker.com/products/docker-desktop/).
2. Install and restart if prompted.
3. Start **Docker Desktop** and wait until it shows **Running**.

Verify in PowerShell:

```powershell
docker --version
docker compose version
```

### Step 2: Get the project

If you do not have the code yet:

```powershell
cd c:\xampp\htdocs
git clone https://github.com/dinosaur2810/uphub.git UpHub
cd UpHub
```

If you already have it:

```powershell
cd c:\xampp\htdocs\UpHub
```

### Step 3: Build and start containers

```powershell
docker compose up -d --build
```

Uses `docker-compose.yml` + `docker-compose.override.yml` (port **8081**). Jenkins CI uses port **8082** only and does not load the override file.

What this does:

| Service | Role |
|---------|------|
| **db** | MariaDB with database `uphub` (initialized from `sql/install.sql`) |
| **web** | PHP 8.2 + Apache (UpHub app) |

First run may take **5–15 minutes** (download images + Composer install in build).

### Step 4: Check containers

```powershell
docker compose ps
```

Both services should be **running**; `web` should show **healthy** when ready.

### Step 5: Open the app

In your browser:

**http://localhost:8081/UpHub/**

(Same `/UpHub` path as XAMPP. `http://localhost:8081/` redirects automatically.)

Login: **http://localhost:8081/UpHub/login.php**

### Step 6: View logs (if something fails)

```powershell
docker compose logs web --tail 50
docker compose logs db --tail 50
```

### Step 7: Stop Docker

Stop containers (keep database data):

```powershell
docker compose down
```

Stop and **delete** database volume (fresh DB next time):

```powershell
docker compose down -v
```

### Step 8: Redo Docker from scratch

Use this when you want a clean rebuild:

```powershell
cd c:\xampp\htdocs\UpHub
docker compose down -v
docker compose build --no-cache
docker compose up -d
```

Then open http://localhost:8081 again.

### Optional: Google Maps API key

Create `.env` in the project root:

```env
GOOGLE_MAPS_API_KEY=your_google_maps_key
```

Restart:

```powershell
docker compose up -d
```

### Docker credentials (reference)

| Setting | Value |
|---------|--------|
| App URL | http://localhost:8081/UpHub |
| DB host (inside Docker) | `db` |
| DB name | `uphub` |
| DB user | `uphub` |
| DB password | `uphub` |

---

## Part 2 — Jenkins (Build → Test → Deploy Pipeline)

### Prerequisites

- Jenkins running (Windows service): http://localhost:8080  
- Plugins installed (from Jenkins plugin manager): **Pipeline**, **Git**, **GitHub**, **Docker** (Docker CLI on the Jenkins machine)  
- **Git for Windows** installed at `C:\Program Files\Git\`  
- **Docker Desktop** running (same as Part 1)
- **Credentials API** registered in your system (for authentication)

### Step 1: Log in to Jenkins

1. Open http://localhost:8080  
2. Sign in with your Jenkins user (e.g. `tagayanfinal`).

### Step 3: Register Credentials API Token (for remote access)

1. Click your username (top right) → **Security** (or **Configure**).  
2. **API Token** → **Add new Token** → name it `uphub-remote-api` → **Generate**.  
3. Copy the token immediately (shown only once).  
4. **Save**.

Store credentials securely:

- **Username:** your Jenkins login id (e.g. `tagayanfinal`)  
- **API Token:** the token (not your login password)

You'll use this for remote builds: `http://localhost:8080/job/{job-name}/build?token=uphub-remote-api`

### Step 4: Configure Remote Access (Manage Jenkins → Security)

1. **Manage Jenkins** → **Security**  
2. Enable **Trigger builds remotely** if not already enabled  
3. **Authorization** → Set to **Logged-in users can do anything** or **Project-based Matrix Authorization**  
4. Allow API token authentication  
5. **Save**

### Step 5: Point Jenkins to Git (one-time)

Jenkins runs as a Windows service and must find `git.exe`.

1. **Manage Jenkins** → **Tools**  
2. **Git installations** → **Add Git** (or edit **Default**)  
3. Set **Path to Git executable** to:

   ```
   C:\Program Files\Git\bin\git.exe
   ```

4. **Save**.
### Step 6: Create Pipeline Jobs (Build, Test, Deploy)

Create three separate pipeline jobs for the CI/CD workflow:

#### Job 1: `uphub` (Build Stage)

1. Jenkins home → **New Item**  
2. **Item name:** `uphub`  
3. Select **Pipeline** → **OK**  
4. **General**  
   - Description: `UpHub — Build Stage (GitHub + Docker)`  
   - ☑ **Build after other projects are built** → none (this is the first job)
5. **Build Triggers**  
   - ☑ **GitHub hook trigger for GITScm polling** (for GitHub webhooks)  
   - Or ☑ **Poll SCM** → schedule `H/15 * * * *` (every 15 min)  
   - ☑ **Trigger builds remotely** → Auth token: `uphub-remote-api`
6. **Pipeline**  
   - **Definition:** Pipeline script from SCM  
   - **SCM:** Git  
   - **Repository URL:** `https://github.com/dinosaur2810/uphub.git`  
   - **Credentials:** (none for public repo, or add GitHub PAT if private)  
   - **Branch Specifier:** `*/main`  
   - **Script Path:** `Jenkinsfile`
7. **Save**

#### Job 2: `uphub2` (Test Stage)

1. Jenkins home → **New Item**  
2. **Item name:** `uphub2`  
3. Select **Pipeline** → **OK**  
4. **General**  
   - Description: `UpHub — Test Stage`  
   - ☑ **Build after other projects are built** → `uphub` (triggers after build completes)
5. **Build Triggers**  
   - ☑ **Build after other projects are built** → Projects: `uphub`
   - ☑ **Trigger builds remotely** → Auth token: `uphub-remote-api`
6. **Pipeline**  
   - **Definition:** Pipeline script from SCM  
   - **SCM:** Git  
   - **Repository URL:** `https://github.com/dinosaur2810/uphub.git`  
   - **Credentials:** (same as uphub)  
   - **Branch Specifier:** `*/main`  
   - **Script Path:** `Jenkinsfile`
7. **Save**

#### Job 3: `uphub3` (Deploy Stage)

1. Jenkins home → **New Item**  
2. **Item name:** `uphub3`  
3. Select **Pipeline** → **OK**  
4. **General**  
   - Description: `UpHub — Deploy Stage`  
   - ☑ **Build after other projects are built** → `uphub2` (triggers after test completes)
5. **Build Triggers**  
   - ☑ **Build after other projects are built** → Projects: `uphub2`
   - ☑ **Trigger builds remotely** → Auth token: `uphub-remote-api`
6. **Pipeline**  
   - **Definition:** Pipeline script from SCM  
   - **SCM:** Git  
   - **Repository URL:** `https://github.com/dinosaur2810/uphub.git`  
   - **Credentials:** (same as uphub)  
   - **Branch Specifier:** `*/main`  
   - **Script Path:** `Jenkinsfile`
7. **Save**

### Step 7: Create `.env.jenkins` for local deployment

```powershell
cd c:\xampp\htdocs\UpHub
copy .env.jenkins.example .env.jenkins
notepad .env.jenkins
```

Example `.env.jenkins`:

```env
JENKINS_URL=http://localhost:8080
JENKINS_USER=tagayanfinal
JENKINS_API_TOKEN=paste_your_token_here
JENKINS_REMOTE_TOKEN=uphub-remote-api
```

### Step 8: Run the pipeline manually

**Option A: Start the build chain locally (uphub → uphub2 → uphub3)**

```powershell
cd c:\xampp\htdocs\UpHub
.\scripts\deploy-and-build.ps1 -Message "my update"
```

This will push to GitHub and trigger `uphub` job via API. The chain continues:
- ✓ Build (`uphub`) runs  
- ✓ Test (`uphub2`) runs after build succeeds  
- ✓ Deploy (`uphub3`) runs after test succeeds

**Option B: Build only (no git push)**

```powershell
.\scripts\trigger-jenkins-build.ps1
```

**Option C: Manual UI trigger**

1. Open http://localhost:8080/job/uphub/  
2. Click **Build Now**  
3. Watch the chain progress: uphub → uphub2 → uphub3
4. Click each build number → **Console Output** to see logs

### Step 9: Remote Trigger via API (from anywhere)

Trigger the build chain remotely using your credentials API:

```powershell
$uri = "http://localhost:8080/job/uphub/build?token=uphub-remote-api"
$user = "tagayanfinal"
$token = "your_api_token_here"
$auth = [Convert]::ToBase64String([Text.Encoding]::ASCII.GetBytes(("$user`:$token")))

Invoke-RestMethod -Uri $uri `
  -Method Post `
  -Headers @{ Authorization = "Basic $auth" } `
  -ErrorAction Stop

Write-Host "Build triggered successfully!"
```

Or from Linux/Mac:

```bash
curl -X POST http://localhost:8080/job/uphub/build?token=uphub-remote-api \
  --user tagayanfinal:your_api_token_here
```

### Step 10: Recreate jobs if deleted

| Error | Fix |
|-------|-----|
| `Cannot run program "git.exe"` | Set Git path in **Manage Jenkins → Tools** (Step 2) |
| `Couldn't find any revision to build` | Set branch to `*/main` and URL to `https://github.com/dinosaur2810/uphub.git` |
| `container name already in use` | Run `docker compose down -v` in project folder; pull latest `main` (includes container name fix) |
| `Failed to connect to localhost port 8082` | Start Docker Desktop; ensure Jenkins can run `docker compose` |
| API `401 Unauthorized` | Use Jenkins **username** + **API token**; set variables before `Invoke-RestMethod` |
| `Remote trigger not working` | Ensure **Trigger builds remotely** is checked; verify auth token in URL |
| Jobs not chaining (uphub2 after uphub) | Check **Build after other projects are built** with correct job name; ensure build is stable |

### Step 12: Reload Jenkins after manual config changes

If you edit files under `C:\ProgramData\Jenkins\.jenkins\` by hand:

1. **Manage Jenkins** → **Reload configuration from disk**  

Or restart the service (Administrator PowerShell):

```powershell
Restart-Service -Name Jenkins -Force
```

---

## Part 3 — Quick Reference

### Jenkins URLs

| Job | URL | Purpose |
|-----|-----|---------|
| **Build** | http://localhost:8080/job/uphub/ | Compile + validate code |
| **Test** | http://localhost:8080/job/uphub2/ | Run integration tests |
| **Deploy** | http://localhost:8080/job/uphub3/ | Deploy to production |
| **Dashboard** | http://localhost:8080 | View all jobs |

### Docker

```powershell
cd c:\xampp\htdocs\UpHub
docker compose up -d --build    # start
docker compose ps                 # status
docker compose down               # stop
docker compose down -v            # stop + wipe DB
```

### Jenkins Actions

| Action | How |
|--------|-----|
| Trigger build chain locally | `.\scripts\deploy-and-build.ps1 -Message "update"` |
| Trigger build (no push) | `.\scripts\trigger-jenkins-build.ps1` |
| Manual build (UI) | Visit job URL → **Build Now** |
| View console | Click build number → **Console Output** |
| Remote API trigger | `curl -X POST http://localhost:8080/job/uphub/build?token=uphub-remote-api --user user:token` |

### Credentials

| Item | Value |
|------|-------|
| Jenkins URL | http://localhost:8080 |
| Username | your_jenkins_user (e.g. `tagayanfinal`) |
| API Token | generated in **Security** (not your login password) |
| Remote Token | `uphub-remote-api` |

### Related docs

- [SETUP_GUIDE.md](../SETUP_GUIDE.md) — XAMPP, maps, email  
- [JENKINS-GITHUB-WEBHOOK.md](JENKINS-GITHUB-WEBHOOK.md) — GitHub webhooks & token types  
