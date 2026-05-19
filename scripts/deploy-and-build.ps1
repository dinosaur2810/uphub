# Push UpHub to GitHub, then trigger Jenkins to pull and run the Docker pipeline.
#
# Prerequisites:
#   - Git installed
#   - .env.jenkins with JENKINS_USER and JENKINS_API_TOKEN
#   - git remote origin -> https://github.com/dinosaur2810/uphub.git
#
# Usage:
#   .\scripts\deploy-and-build.ps1
#   .\scripts\deploy-and-build.ps1 -Message "fix login"

param(
    [string]$Message = "Deploy from local machine",
    [switch]$SkipPush,
    [switch]$SkipBuild
)

$ErrorActionPreference = "Stop"
$repoRoot = Split-Path -Parent (Split-Path -Parent $MyInvocation.MyCommand.Path)
Set-Location $repoRoot

$git = "C:\Program Files\Git\bin\git.exe"
if (-not (Test-Path $git)) { $git = "git" }

if (-not $SkipPush) {
    Write-Host "==> Git status"
    & $git status --short

    $changes = & $git status --porcelain
    if ($changes) {
        Write-Host "==> Commit and push"
        & $git add -A
        & $git commit -m $Message
    } else {
        Write-Host "No local changes to commit."
    }

    Write-Host "==> Push to origin main"
    & $git push origin main
}

if (-not $SkipBuild) {
    Write-Host "==> Trigger Jenkins build"
    & (Join-Path $PSScriptRoot "trigger-jenkins-build.ps1")
}

Write-Host "Done."
