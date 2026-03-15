---
name: test-gate
description: >
  Run tests, check coverage, and generate missing tests for staged changes.
  Activates when preparing commits, running tests, checking coverage, or
  when the user mentions testing, test coverage, or test generation.
---

# Test Gate

## When to Apply

Activate this skill when:

- Preparing code for commit (test validation)
- Running or debugging tests
- Checking test coverage
- Generating tests for untested code

## Phase 1 — Run Tests

Execute the project test suite.

### By Stack

| Stack | Command |
|-------|---------|
| Node | `npm test` |
| PHP/Laravel | `php artisan test --compact` |
| Python | `pytest` |
| Go | `go test ./...` |
| Rust | `cargo test` |
| Java (Maven) | `mvn test` |
| Java (Gradle) | `./gradlew test` |
| .NET | `dotnet test` |

### Rules

- **All tests must pass. Abort commit if tests fail.**
- Run the minimum set of tests needed — filter by changed files when possible.
- For Laravel: `php artisan test --compact --filter=TestName` for targeted runs.

## Phase 2 — Test Coverage Check

If coverage tools are available, check for coverage regressions.

### By Stack

| Stack | Tool |
|-------|------|
| JS/TS | Jest `--coverage` / `nyc` |
| Python | `coverage.py` / `pytest-cov` |
| PHP | PHPUnit `--coverage-text` |

### Rules

- Warn if coverage drops significantly (> 2% decrease).
- Report uncovered lines in changed files.
- Do not block commit for coverage alone.

## Phase 3 — AI Test Generation

If new logic appears in staged changes without corresponding tests, generate them.

### Rules

- Tests must be deterministic (no random data, no time-dependent assertions).
- Avoid external services — mock all dependencies.
- Cover edge cases: empty inputs, boundary values, error paths.
- Follow existing test conventions (check sibling test files for style).
- For Laravel: use PHPUnit classes, not Pest. Use `php artisan make:test --phpunit`.
- Run generated tests to confirm they pass before staging.
