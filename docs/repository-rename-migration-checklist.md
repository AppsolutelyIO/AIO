# Repository Rename Migration Checklist

This checklist is for renaming the repository and (optionally) migrating the Composer package name with minimal risk.

## Scope Levels

- Level 1 (Low risk): rename GitHub repository only.
- Level 2 (Medium risk): migrate Composer package name.
- Level 3 (High risk, not recommended now): full namespace/brand refactor.

## Phase 1: Repository Rename Only (Recommended First)

### 1. Preparation

- [ ] Confirm no release is in progress.
- [ ] Create backup tag on current default branch.

```bash
git checkout main
git pull --ff-only
git tag -a pre-repo-rename-$(date +%Y%m%d) -m "backup before repository rename"
git push --tags
```

### 2. Rename on GitHub

- [ ] Rename repository in GitHub settings.
- [ ] Verify old URL redirects to new URL.

### 3. Update Consumer Project (appsolutely/site)

- [ ] Update VCS repository URL in `composer.json`.
- [ ] Keep package name as `appsolutely/aio` for now.

```bash
cd /Volumes/Data/Projects/appsolutely/site
# edit composer.json repositories[].url
composer update appsolutely/aio
```

### 4. Validation

- [ ] Admin login works.
- [ ] Grid list page loads.
- [ ] Switch inline toggle works.
- [ ] Form submit + file upload works.
- [ ] Permission-protected pages still enforce auth.

### 5. Release

- [ ] Tag and release a stable version.
- [ ] Update README links and internal docs.

## Phase 2: Composer Package Rename (Do After Phase 1 Is Stable)

### 1. Package Changes (in this repo)

- [ ] Update `composer.json` package `name` to the new vendor/package.
- [ ] Update README install commands.
- [ ] Keep PHP namespace as `Dcat\\Admin` to reduce migration risk.

### 2. Consumer Migration (appsolutely/site)

- [ ] Replace old package in `require` with new package name.
- [ ] Keep VCS repository URL correct.

```bash
cd /Volumes/Data/Projects/appsolutely/site
# edit composer.json require + repositories
composer update
```

### 3. Regression Verification

- [ ] Run backend test suite.
- [ ] Smoke test admin pages and dcat-api endpoints.
- [ ] Verify published assets and config still load correctly.

### 4. Compatibility Window

- [ ] Announce migration deadline.
- [ ] Keep old package path/version available during transition.

## Rollback Plan

### Phase 1 rollback

- [ ] Revert consumer `composer.json` VCS URL.
- [ ] `composer update appsolutely/aio`.

### Phase 2 rollback

- [ ] Restore old package name in both package and consumer.
- [ ] Re-run `composer update`.
- [ ] Re-tag hotfix if needed.

## Notes

- Avoid full namespace rename (`Dcat\\Admin` -> new namespace) in the same cycle.
- Do migration in small releases: repository rename first, package rename second.
