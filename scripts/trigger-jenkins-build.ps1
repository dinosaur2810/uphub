# Queue a Jenkins build for UpHub via REST API.
#
# Setup once (PowerShell):
#   Copy .env.jenkins.example to .env.jenkins and fill in your API token.
#   Or set environment variables manually.
#
# Usage:
#   .\scripts\trigger-jenkins-build.ps1
#   .\scripts\trigger-jenkins-build.ps1 -JenkinsUrl "http://192.168.1.10:8080"

param(
    [string]$JenkinsUrl = $env:JENKINS_URL,
    [string]$JobName = "uphub"
)

$ErrorActionPreference = "Stop"

$scriptRoot = Split-Path -Parent $MyInvocation.MyCommand.Path
$envFile = Join-Path (Split-Path -Parent $scriptRoot) ".env.jenkins"
if (Test-Path $envFile) {
    Get-Content $envFile | ForEach-Object {
        if ($_ -match '^\s*([^#=]+)=(.*)$') {
            $name = $matches[1].Trim()
            $value = $matches[2].Trim().Trim('"').Trim("'")
            Set-Item -Path "env:$name" -Value $value
        }
    }
}

if (-not $JenkinsUrl) { $JenkinsUrl = "http://localhost:8080" }
$JenkinsUrl = $JenkinsUrl.TrimEnd("/")

$user = $env:JENKINS_USER
$apiToken = $env:JENKINS_API_TOKEN

if (-not $user -or -not $apiToken) {
    Write-Error @"
Missing credentials. Set in .env.jenkins (copy from .env.jenkins.example) or environment:

  JENKINS_USER=tagayanfinal
  JENKINS_API_TOKEN=your-api-token
  JENKINS_URL=http://localhost:8080
"@
    exit 1
}

$b64 = [Convert]::ToBase64String([Text.Encoding]::ASCII.GetBytes("${user}:${apiToken}"))
$headers = @{ Authorization = "Basic $b64" }

$me = Invoke-RestMethod -Uri "$JenkinsUrl/me/api/json" -Headers $headers
Write-Host "Authenticated as $($me.id)"

$crumb = Invoke-RestMethod -Uri "$JenkinsUrl/crumbIssuer/api/json" -Headers $headers
$headers["Jenkins-Crumb"] = $crumb.crumb

$buildUrl = "$JenkinsUrl/job/$JobName/build"
Invoke-WebRequest -Uri $buildUrl -Method Post -Headers $headers -UseBasicParsing | Out-Null
Write-Host "Build queued: $buildUrl/console"
