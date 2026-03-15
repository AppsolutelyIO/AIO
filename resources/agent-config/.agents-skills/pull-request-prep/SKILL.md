---
name: pull-request-prep
description: >
  Generate pull request descriptions and review checklists. Activates when
  creating PRs, preparing code for review, or when the user mentions pull
  request, PR, code review, or merge request.
---

# Pull Request Preparation

## When to Apply

Activate this skill when:

- Creating a pull request
- Preparing code for review
- Generating a PR description
- User asks for a review checklist

## Phase 1 — Generate PR Description

### Structure

```markdown
## Summary

High-level explanation of what this PR does and why.

## Changes

- Major feature or change 1
- Refactoring details
- Configuration changes

## Migration

Database migration notes (if applicable):
- New tables or columns
- Data transformations
- Required migration commands

## Breaking Changes

List any breaking changes (if applicable):
- Removed endpoints
- Changed response formats
- Renamed columns

## Tests

- New tests added
- Updated existing tests
- Test coverage status
```

### Rules

- Summary should be 1-3 sentences, focused on the "why".
- Changes section: one bullet per logical change.
- Include migration section only if migrations are present.
- Include breaking changes section only if breaking changes exist.

## Phase 2 — PR Review Checklist

Verify before marking PR ready for review:

### Code Quality

- [ ] Code formatting correct (formatter ran)
- [ ] Lint passes with no errors
- [ ] No excessive complexity introduced

### Security

- [ ] No secrets committed
- [ ] Dependencies are secure (audit passed)
- [ ] No SAST high-severity findings

### Testing

- [ ] All tests pass
- [ ] New tests added for new logic
- [ ] Coverage stable or improved

### Database

- [ ] Migrations are reversible
- [ ] Breaking schema changes documented
- [ ] Migration rollback tested

### Architecture

- [ ] Architecture rules respected
- [ ] No performance regressions detected

### Documentation

- [ ] Changelog updated (if applicable)
- [ ] Version bumped (if applicable)
- [ ] API documentation updated (if applicable)
- [ ] ADR created (if architecture changed)
