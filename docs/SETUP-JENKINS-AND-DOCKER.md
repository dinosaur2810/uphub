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

## Part 2 — Jenkins (new pipeline or recreate after delete)

### Prerequisites

- Jenkins running (Windows service): http://localhost:8080  
- Plugins installed (from Jenkins plugin manager): **Pipeline**, **Git**, **GitHub**, **Docker** (Docker CLI on the Jenkins machine)  
- **Git for Windows** installed at `C:\Program Files\Git\`  
- **Docker Desktop** running (same as Part 1)

### Step 1: Log in to Jenkins

1. Open http://localhost:8080  
2. Sign in with your Jenkins user (e.g. `tagayanfinal`).

### Step 2: Point Jenkins to Git (one-time)

Jenkins runs as a Windows service and must find `git.exe`.

1. **Manage Jenkins** → **Tools**  
2. **Git installations** → **Add Git** (or edit **Default**)  
3. Set **Path to Git executable** to:

   ```
   C:\Program Files\Git\bin\git.exe
   ```

4. **Save**.

### Step 3: Create API token (for scripts / terminal)

1. Click your username (top right) → **Security** (or **Configure**).  
2. **API Token** → **Add new Token** → name it `uphub-cli` → **Generate**.  
3. Copy the token immediately (shown only once).  
4. **Save**.

Use:

- **Username:** your Jenkins login id (e.g. `tagayanfinal`)  
- **Password:** the API token (not your login password unless you choose that)

Local project file (copy from example):

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
```

### Step 4: Create a new Pipeline job

Do this for a **new** job or if the old `uphub` job was **deleted**.

1. Jenkins home → **New Item**  
2. **Item name:** `uphub`  
3. Select **Pipeline** → **OK**  
4. **General**  
   - Description: `UpHub — GitHub + Docker CI`  
5. **Build Triggers** (optional)  
   - ☑ **GitHub hook trigger for GITScm polling** (for webhooks later)  
   - Or ☑ **Poll SCM** → schedule `H/15 * * * *` (every 15 min)  
6. **Pipeline**  
   - **Definition:** Pipeline script from SCM  
   - **SCM:** Git  
   - **Repository URL:**

     ```
     https://github.com/dinosaur2810/uphub.git
     ```

   - **Credentials:** none (public repo) or add GitHub PAT if private  
   - **Branch Specifier:** `*/main`  
   - **Script Path:** `Jenkinsfile`  
7. **Save**

### Step 5: Run the pipeline manually

1. Open http://localhost:8080/job/uphub/  
2. Click **Build Now**  
3. Click build number → **Console Output**

Expected stages:

1. Checkout  
2. Validate  
3. Docker Build  
4. Integration Test (port **8082** in CI; your local app stays on **8081**)

Success ends with: `Finished: SUCCESS`

### Step 6: Recreate pipeline if deleted

If someone deleted the `uphub` job, repeat **Part 2, Step 4** exactly (same name `uphub`, same Git URL and branch).

Nothing in GitHub needs to change — only recreate the job in Jenkins.

Quick checklist:

| Setting | Value |
|---------|--------|
| Job type | Pipeline |
| Job name | `uphub` |
| SCM | Git |
| URL | `https://github.com/dinosaur2810/uphub.git` |
| Branch | `*/main` |
| Script path | `Jenkinsfile` |

Then **Build Now**.

### Step 7: Deploy from your PC (push + build)

After `.env.jenkins` is configured:

```powershell
cd c:\xampp\htdocs\UpHub
.\scripts\deploy-and-build.ps1 -Message "my update"
```

This will:

1. Commit and push to GitHub (`main`)  
2. Trigger Jenkins build via API  

Build only (no git push):

```powershell
.\scripts\trigger-jenkins-build.ps1
```

### Step 8: Fix common Jenkins errors

| Error | Fix |
|-------|-----|
| `Cannot run program "git.exe"` | Set Git path in **Manage Jenkins → Tools** (Step 2) |
| `Couldn't find any revision to build` | Set branch to `*/main` and URL to `https://github.com/dinosaur2810/uphub.git` |
| `container name already in use` | Run `docker compose down -v` in project folder; pull latest `main` (includes container name fix) |
| `Failed to connect to localhost port 8082` | Start Docker Desktop; ensure Jenkins can run `docker compose` |
| API `401 Unauthorized` | Use Jenkins **username** + **API token**; set variables before `Invoke-RestMethod` |

### Step 9: Reload Jenkins after manual config changes

If you edit files under `C:\ProgramData\Jenkins\.jenkins\` by hand:

1. **Manage Jenkins** → **Reload configuration from disk**  

Or restart the service (Administrator PowerShell):

```powershell
Restart-Service -Name Jenkins -Force
```

---

## Part 3 — Quick reference

### Docker

```powershell
cd c:\xampp\htdocs\UpHub
docker compose up -d --build    # start
docker compose ps                 # status
docker compose down               # stop
docker compose down -v            # stop + wipe DB
```

### Jenkins

| Action | How |
|--------|-----|
| Open Jenkins | http://localhost:8080 |
| Open job | http://localhost:8080/job/uphub/ |
| New / recreated pipeline | **New Item** → Pipeline → configure as in Step 4 |
| Manual build | **Build Now** |
| Auto push + build | `.\scripts\deploy-and-build.ps1` |

### Related docs

- [SETUP_GUIDE.md](../SETUP_GUIDE.md) — XAMPP, maps, email  
- [JENKINS-GITHUB-WEBHOOK.md](JENKINS-GITHUB-WEBHOOK.md) — GitHub webhooks & token types  
