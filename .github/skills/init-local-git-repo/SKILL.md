---
name: Initialize Local And Remote Git Repository
description: Global workflow to initialize a repo under C:\Users\matt\git, map to a required GitHub repo name, create an initial commit, and push to a local bare remote mirror.
---

# Initialize Local And Remote Git Repository

## Purpose
Use this skill as a global, reusable workflow to create a repository from a fixed local root and push the first commit to a local bare remote path that mirrors a GitHub-style repo name.

## Scope
- Global (personal): designed for reuse across workspaces.

## Inputs
- `github_repo` (required, example: `my-project`)
- `default_branch` (default: `main`)
- `initial_commit_message` (default: `Initial commit`)

## Fixed Paths
- Local root: `C:\Users\matt\git`
- Remote org root: `C:\github.com\mattryanco`
- Derived local path: `C:\Users\matt\git\<github_repo>`
- Derived remote path: `C:\github.com\mattryanco\<github_repo>.git`

## Preconditions
- Git is installed and available in PATH.
- Parent directories for fixed roots are writable.
- You understand this workflow creates `.git` metadata and may create new directories.

## Workflow
1. Validate `github_repo` is provided and non-empty.
2. Build paths from fixed roots:
    - `local_repo_path = C:\Users\matt\git\<github_repo>`
    - `remote_repo_path = C:\github.com\mattryanco\<github_repo>.git`
3. Create `local_repo_path` if it does not exist.
4. Check local repository state:
   - If `.git` does not exist: initialize repo with `git init -b <default_branch>`.
    - If `.git` exists: prompt the user to reuse this local repo; continue only on confirmation.
5. Create or verify remote repository path:
   - Create parent directory for `remote_repo_path` if needed.
   - If remote path does not exist: create bare repo with `git init --bare <remote_repo_path>`.
   - If remote path exists and is not bare: stop and ask for a different remote path.
6. Configure remote in local repo:
   - If `origin` exists, update it to `remote_repo_path`.
   - Otherwise add `origin`.
7. Create initial commit:
   - Stage all files with `git add -A`.
   - If there are staged changes: `git commit -m <initial_commit_message>`.
   - If no staged changes: `git commit --allow-empty -m <initial_commit_message>`.
8. Push the default branch:
   - `git push -u origin <default_branch>`.

## Decision Points
- Existing local repo detected: prompt to reuse or abort.
- Existing `origin` remote points elsewhere: confirm replace vs keep.
- Remote path exists but is non-bare: abort and request a valid bare remote target.

## Completion Checks
- Local repo initialized:
  - `git -C <local_repo_path> rev-parse --is-inside-work-tree` returns `true`.
- Remote repo is bare:
  - `git --git-dir <remote_repo_path> rev-parse --is-bare-repository` returns `true`.
- Initial commit exists:
  - `git -C <local_repo_path> log --oneline -n 1` returns at least one commit.
- Upstream tracking is configured:
  - `git -C <local_repo_path> branch -vv` shows `<default_branch>` tracking `origin/<default_branch>`.

## PowerShell Command Template
```powershell
param(
    [Parameter(Mandatory = $true)]
    [string]$GitHubRepo,
    [string]$DefaultBranch = "main",
    [string]$InitialCommitMessage = "Initial commit",
    [switch]$ReuseExistingLocalRepo
)

$localRoot = "C:\Users\matt\git"
$remoteRoot = "C:\github.com\mattryanco"

$repoName = $GitHubRepo.Trim()
if ([string]::IsNullOrWhiteSpace($repoName)) {
    throw "GitHubRepo is required."
}

$LocalRepoPath = Join-Path $localRoot $repoName
$RemoteRepoPath = Join-Path $remoteRoot ("{0}.git" -f $repoName)

New-Item -ItemType Directory -Force -Path $LocalRepoPath | Out-Null

if (Test-Path (Join-Path $LocalRepoPath ".git")) {
    if (-not $ReuseExistingLocalRepo) {
        throw "Local repo already exists. Re-run with -ReuseExistingLocalRepo to confirm reuse: $LocalRepoPath"
    }
} else {
    git -C $LocalRepoPath init -b $DefaultBranch
}

$remoteParent = Split-Path $RemoteRepoPath -Parent
if (-not [string]::IsNullOrWhiteSpace($remoteParent)) {
    New-Item -ItemType Directory -Force -Path $remoteParent | Out-Null
}

if (-not (Test-Path $RemoteRepoPath)) {
    git init --bare $RemoteRepoPath
} else {
    $isBare = git --git-dir $RemoteRepoPath rev-parse --is-bare-repository 2>$null
    if ($isBare -ne "true") {
        throw "Remote path exists but is not a bare repository: $RemoteRepoPath"
    }
}

$originExists = git -C $LocalRepoPath remote get-url origin 2>$null
if ($LASTEXITCODE -eq 0) {
    git -C $LocalRepoPath remote set-url origin $RemoteRepoPath
} else {
    git -C $LocalRepoPath remote add origin $RemoteRepoPath
}

git -C $LocalRepoPath add -A
git -C $LocalRepoPath diff --cached --quiet
if ($LASTEXITCODE -eq 1) {
    git -C $LocalRepoPath commit -m $InitialCommitMessage
} else {
    git -C $LocalRepoPath commit --allow-empty -m $InitialCommitMessage
}

git -C $LocalRepoPath push -u origin $DefaultBranch
```

## Done Criteria
This skill is complete when all completion checks pass with no errors.