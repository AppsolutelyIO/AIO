---
name: database-safety
description: >
  Detect breaking database schema changes, verify migration rollback safety,
  and simulate migration workflows. Activates when staged changes include
  migrations, schema changes, or when the user mentions migrations, database
  changes, or rollback safety.
---

# Database Safety

## When to Apply

Activate this skill when:

- Staged changes include database migrations
- Reviewing schema changes for breaking impact
- Verifying migration rollback safety
- Simulating migration apply/rollback cycles

## Phase 1 — Detect Schema Changes

Inspect staged migrations for breaking changes.

### Migration Directories

- `database/migrations` (Laravel)
- `prisma/migrations` (Prisma)
- `schema.sql`, `*.sql` files

### Breaking Change Indicators

- `DROP TABLE`
- `DROP COLUMN`
- `ALTER COLUMN TYPE` (data type changes)
- `RENAME COLUMN`
- `RENAME TABLE`
- Removing or changing unique/index constraints

### Rules

- If breaking changes detected, the commit type must include `!` suffix:
  ```
  feat(db)!: change users.email to unique
  ```
- Report each breaking change with table name and column affected.

## Phase 2 — Migration Rollback Check

Ensure all migrations are reversible.

### By Framework

| Framework | Requirement |
|-----------|-------------|
| Laravel | `down()` method must exist in migration class |
| Rails | `change` must be reversible, or `up`/`down` defined |
| Prisma | Rollback strategy defined |

### Rules

- Warn if `down()` method is missing or empty.
- Warn if `down()` does not properly reverse `up()` operations.
- For destructive operations in `up()`, verify `down()` can restore data.

## Phase 3 — Migration Rollback Simulation

If migrations exist in staged changes, simulate the workflow.

### Workflow

1. Apply migration: `php artisan migrate`
2. Rollback migration: `php artisan migrate:rollback --step=1`
3. Re-apply migration: `php artisan migrate`

### Rules

- Schema must remain valid after apply → rollback → re-apply cycle.
- Report any errors during simulation.
- Only simulate in development/testing environments — never in production.
- Ask user for confirmation before running simulation.
