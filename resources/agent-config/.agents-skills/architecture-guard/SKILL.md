---
name: architecture-guard
description: >
  Validate architecture boundaries and detect performance regressions in
  staged changes. Activates when reviewing code structure, checking layer
  dependencies, or when the user mentions architecture rules, N+1 queries,
  or performance regressions.
---

# Architecture Guard

## When to Apply

Activate this skill when:

- Reviewing staged changes for architectural violations
- Checking layer dependency rules (controller → service → repository)
- Detecting potential performance regressions
- User asks about architecture compliance

## Phase 1 — Architecture Rule Check

Validate that staged changes respect architecture boundaries.

### Layer Rules (Service/Repository Pattern)

| Layer | Allowed Dependencies | Forbidden |
|-------|---------------------|-----------|
| Controllers | Services, Form Requests | Repositories, Models (direct queries), DB facade |
| Services | Repositories, other Services, DTOs | DB facade, direct Eloquent queries |
| Repositories | Eloquent Models, Query Builder | Controllers, Services |
| Models | Other Models (relationships) | Services, Controllers |

### Common Violations

- Controllers accessing the database directly
- Services bypassing repositories for data access
- Domain logic leaking into controllers
- Repositories containing business logic
- Using `DB::` facade instead of repository methods
- Using `env()` outside config files

### DDD / Hexagonal Checks (if applicable)

- Domain layer must not depend on infrastructure
- Core domain must not import adapters
- Application services must not depend on framework internals

### Rules

- Warn if architectural violations detected.
- Report the file, line, and violated rule.
- Suggest the correct layer for the offending code.

## Phase 2 — Performance Regression Detection

Detect potential performance issues in staged changes.

### Indicators

- **N+1 queries**: Loops that trigger individual queries (missing eager loading)
- **Nested loops**: Over large datasets or database collections
- **Unindexed queries**: WHERE clauses on non-indexed columns
- **Blocking IO**: Synchronous calls in async code paths
- **Missing pagination**: Queries that fetch all records without limits
- **Unnecessary eager loading**: Loading relations not used in the code path

### Tools (if available)

| Tool | Stack |
|------|-------|
| Laravel Debugbar | PHP/Laravel |
| Django Silk | Python |
| ESLint performance rules | JS/TS |
| Go benchmarks | Go |
| `cargo bench` | Rust |

### Rules

- Warn if regression risk exists.
- Suggest specific fixes (add `with()`, add index, use pagination).
- Do not block commit — report as advisory.
