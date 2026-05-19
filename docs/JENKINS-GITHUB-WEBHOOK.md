# Jenkins + GitHub for UpHub

## Token types (do not mix them up)

| Token | Where it is created | Used for |
|-------|---------------------|----------|
| **Git notifyCommit access token** | Manage Jenkins → Security → *Git plugin notifyCommit access tokens* | `GET/POST /git/notifyCommit?url=...&token=SECRET` |
| **User API token** | User → Security → API Token | PowerShell/curl `Basic tagayanfinal:TOKEN` |
| **Remote build token** | Job → Trigger builds remotely | `/job/uphub/build?token=SECRET` |

The label **679d535b81250f17583ad824d2e3e7ce** is only the **token name** in Jenkins. The **secret** was shown once when you clicked **Generate**. If you did not save it, click **Revoke** and create a new token.

## Option A — GitHub Push trigger (already on `uphub` job)

Uses the **GitHub plugin** (`/github-webhook/`).

1. In Jenkins: **Manage Jenkins** → **System** → **GitHub** → add GitHub Server with a **Personal Access Token** (repo scope).
2. On GitHub: repo **Settings** → **Webhooks** → **Add webhook**
   - **Payload URL:** `http://YOUR_PUBLIC_HOST:8080/github-webhook/`
   - **Content type:** `application/json`
   - **Events:** Just the push event
3. GitHub must reach Jenkins. `localhost` does not work from GitHub; use a tunnel (ngrok, Cloudflare Tunnel) or a server with a public IP.

## Option B — Git notifyCommit (your access token)

**Payload URL** (replace `YOUR_SECRET` with the value shown once at token creation):

```
http://YOUR_PUBLIC_HOST:8080/git/notifyCommit?url=https://github.com/dinosaur2810/uphub.git&token=YOUR_SECRET
```

Test from a machine that can reach Jenkins:

```powershell
$secret = "YOUR_NOTIFYCOMMIT_SECRET"
$url = "https://github.com/dinosaur2810/uphub.git"
Invoke-WebRequest -Uri "http://localhost:8080/git/notifyCommit?url=$url&token=$secret" -UseBasicParsing
```

## Manual build (no tokens)

http://localhost:8080/job/uphub/ → **Build Now**
