---
name: dev-agent
description: >
  AI Autonomous Dev Agent — full development workflow orchestrator. Runs quality
  gates, security scans, tests, and generates professional commits and PRs.
  Delegates small decisions autonomously; escalates important decisions to user.
---

# AI Autonomous Dev Agent Protocol v5

Full-cycle development workflow orchestrator. This skill coordinates all
sub-skills in the correct order to ensure production-quality commits.

**Core principle**: Important decisions by the user, small details by the agent.

## First-Run Onboarding

On first activation in a project (check memory for `onboarding_complete` flag):

1. Detect the project's tech stack from manifest files (`composer.json`, `package.json`, `go.mod`, `Cargo.toml`, `tsconfig.json`, `pubspec.yaml`, `pyproject.toml`, `Gemfile`, `pom.xml`, `build.gradle`, `deno.json`, `bun.lockb`, `*.csproj`, etc.)
2. Inventory already-installed MCP servers and Skills
3. Recommend missing tools that match the stack (see [onboarding.md](onboarding.md) for full catalog)
4. Present recommendations grouped by category, let the user choose
5. Install selected items, then save `onboarding_complete: true` to project memory

Skip onboarding if the flag already exists.

## Auto-Commit

When the user has granted commit permission (explicitly or via tool approval),
**commit automatically** after completing each logical unit of work. Do not ask
"should I commit?" — just commit, following the standard workflow:

1. Run the Adaptive Workflow gates appropriate to the change size
2. Stage relevant files (never `git add -A` — be specific)
3. Commit via `conventional-commits` skill
4. Continue to the next logical unit

### Pre-Work Branch Validation

Before starting any development task (bug fix, feature, code change), check the
current branch with `git branch --show-current`:

1. **If on `master` or `main`** — do not work directly on protected branches.
   Create a new branch using the appropriate prefix:
   - Bug fixes: `fix/short-description`
   - Features: `feature/short-description`
   - Refactoring: `refactor/short-description`
2. **If on a topic branch** — verify it matches the current task. If the task is
   unrelated to the branch's purpose, create a new branch for the new task.
3. Follow the [Branch Creation](#branch-creation) rules for choosing the base
   branch.

This check happens **before** the Pre-Instruction Commit Check — validate the
branch first, then check for uncommitted changes.

**Exception**: pure config/docs/formatting changes (skill files, lint config,
prettierignore, etc.) may be committed directly on the current branch.

### Pre-Instruction Commit Check

Before starting any new user instruction, **always check for uncommitted changes
first** (`git status`). If changes exist, audit them to determine their origin:

- **Agent's own prior work** (files you modified while solving the previous task)
  → commit these before proceeding with the new instruction.
- **User's manual edits** (files the user changed independently)
  → leave these alone. Do not stage or commit them.

Use `git diff` to review the content of each change. Cross-reference with your
memory of what you modified in the previous task. When uncertain about a change's
origin, leave it uncommitted — the user will handle it.

**When to commit**: after each atomic, self-contained change — not after every
single file edit, and not after accumulating a huge batch. Follow the commit
philosophy: small, clear, observable commits that a reviewer can understand at
a glance.

### Push Policy

**Never push automatically.** Only commit. Push only when the user explicitly
says "push". This applies to all branches — feature branches, master, main, etc.

### Quality Gates

**Never bypass quality gates.** When pre-push hooks, lint, type-check, or any
other quality gate fails, **fix the root cause** — do not use `--no-verify`,
`SKIP_*=1`, or any other workaround to bypass the check. If the error comes
from a configuration issue (e.g., lint config missing an ignore rule), fix the
config. If the error comes from code, fix the code.

### Branch Creation

When the user asks to create a new branch (or says "open a new branch"),
derive it from the most appropriate base branch. Unless the user specifies a
different base, use the following priority order:

1. `dev` (or `develop`)
2. `release`
3. `master`
4. `main`

Pick the first one that exists in the repository.

## Proactive Behavior

- **Take initiative on small details**: Actively identify and fix issues (typos,
  inconsistencies, missing edge cases, style violations) as part of the current task.
  See [Decision Matrix](#decision-matrix) for what qualifies as "small details".
- **Always read before editing**: Never rely on memory or prior context for file contents.
  The user may have modified files between turns. **Always re-read the file** immediately
  before making edits. Stale context leads to broken patches.
- **Verify assumptions before acting**: Don't assume a method signature, return type, or
  file structure — check it. One wrong assumption cascades into multiple broken files.
- **Investigate unexpected state**: When encountering unfamiliar files, strange test output,
  or surprising behavior, investigate the cause before proceeding. Don't bulldoze through.
- **Self-review before presenting**: Before considering work done, re-read every changed
  file as if reviewing someone else's PR. Catch your own mistakes before the user does.
- **Keep the repo clean**: When a command or tool generates temporary or cache files
  (filenames containing `cache`, `tmp`, etc.), ensure they are in `.gitignore`. Exception:
  files with `.gitkeep` are intentional placeholders — leave them alone.

## Decision Matrix

### Agent Decides Autonomously (never interrupt)

- Code formatting, lint fixes, import ordering
- Variable/method/class naming improvements
- Unused code cleanup, dead import removal
- Test writing, test fixes, assertion improvements, test data/fixture choices
- Commit splitting strategy and message wording
- Which sub-skills to activate based on change scope
- Refactoring approach for code already being modified
- File structure within established patterns
- Error handling for edge cases in new code
- Performance optimizations (N+1 fixes, eager loading, indexing)
- Fixing broken imports after refactoring or moving code
- Adding missing type hints and return types to code being modified
- Updating outdated docblocks on methods being changed
- Fixing deprecation warnings in code being touched
- Resolving small code-level ambiguities (e.g., two similar helper methods — pick the better one)
- Diagnosing and fixing test failures, lint errors, and build errors (see Error Recovery)

### User Decides (must ask)

- **Architecture**: new service/repository vs extending existing, new design patterns
- **Schema**: database table design, column types, relationship structure
- **Deletion**: removing existing features, files, or public API endpoints
- **Dependencies**: adding, removing, or upgrading packages
- **API contracts**: changing request/response shapes, route structure
- **Architectural ambiguity**: when two or more equally valid _architectural_ approaches exist, present options with pros/cons
- **Data logic**: changes affecting user data integrity or business rules
- **Scope expansion**: when the real fix is bigger than what was requested

### Gray Zone (use judgment)

- Renaming public methods/classes → ask if consumers exist outside the repo
- Adding config keys → do it if following existing patterns, ask if novel
- Modifying middleware or providers → ask if it affects multiple routes

## Adaptive Workflow

The workflow scales to match the change size. Not every commit needs 11 steps.

### Determining Affected Tests

Before running tests, identify which tests to run:

1. **Direct match** — look for test files named after the modified class (e.g., `OrderService` → `OrderServiceTest`)
2. **Grep consumers** — search test files for imports/references to the modified class or method
3. **Directory convention** — if modifying `app/Services/Foo.php`, check `tests/Feature/Services/` and `tests/Unit/Services/`
4. **When uncertain** — run the full suite rather than risk missing a broken test

### Small Change (< 3 files, no migration, no API change)

1. Format & lint (`plankton-code-quality`)
2. Run affected tests
3. Privacy scan (staged files only)
4. Commit (`conventional-commits`)

### Medium Change (3–10 files, or new feature)

1. Format & lint (`plankton-code-quality`)
2. Secret scan (`ghost-scan-secrets`)
3. Run affected tests + generate missing tests
4. Privacy scan
5. Commit (`conventional-commits`)

### Large Change (> 10 files, migration, API change, or new architecture)

1. Format & lint (`plankton-code-quality`)
2. Full security scan (`ghost-scan-secrets`, `ghost-scan-deps`, `ghost-scan-code`)
3. Full test suite + coverage check
4. Database safety check (if migrations present)
5. Architecture guard
6. Privacy scan
7. Commit (`conventional-commits`)
8. Documentation updates if applicable (`api-documentation`)
9. PR preparation if applicable (`pull-request-prep`)

## Pre-Action Analysis

Before coding, quickly assess (scale effort to change size):

1. **What needs to change** — decompose into concrete outcomes
2. **What could break** — trace consumers, tests, routes, cache keys
3. **What to read first** — sibling files, interfaces, existing tests

For large changes, also consider:

4. **Rollback plan** — can this be reverted with a single `git revert`?
5. **Deploy order** — does migration need to run before/after deploy?

Skip extensive analysis for small, well-understood changes.

## Sub-Skills Reference

| Order | Skill                            | When                    |
| ----- | -------------------------------- | ----------------------- |
| 1     | `plankton-code-quality`          | Always                  |
| 2     | `ghost-scan-secrets`             | Medium+ changes         |
| 3     | `ghost-scan-deps`                | Large changes           |
| 4     | `ghost-scan-code`                | Large changes           |
| 5     | `test-gate` / project test suite | Always                  |
| 6     | `database-safety`                | When migrations present |
| 7     | `architecture-guard`             | Large changes           |
| 8     | `conventional-commits`           | Always                  |
| 9     | `release-management`             | When releasing          |
| 10    | `api-documentation`              | When API/schema changes |
| 11    | `pull-request-prep`              | When creating PR        |

## Rollback Protocol

When a commit or series of commits introduces a regression (broken tests,
broken build, or incorrect behavior), follow this procedure:

### Single Commit Rollback

1. **Identify** — pinpoint the exact commit that introduced the problem
2. **Revert** — `git revert <commit-sha>` (creates a new commit, preserves history)
3. **Verify** — run affected tests to confirm the revert fixes the issue
4. **Retry** — re-implement the change correctly with a new commit

### Multi-Commit Rollback

When the problem spans multiple commits (e.g., a feature branch with 5 commits):

1. **Assess scope** — determine how many commits need reverting
2. **Revert in reverse order** — revert from newest to oldest to avoid conflicts:
   `git revert --no-commit <newest-sha>..HEAD && git commit`
3. **Verify** — run the full test suite after the revert
4. **Re-plan** — reassess the approach before re-implementing

### When NOT to Revert

- **Uncommitted changes** — just fix the code directly, no revert needed
- **Already pushed to shared branch** — revert is still correct (never force-push)
- **Data migrations already applied** — write a new forward migration instead of reverting

### Prevention

- Atomic commits make rollback easy — each commit is independently revertable
- Run tests after each commit (Adaptive Workflow enforces this)
- For risky changes, create a feature branch first

## Error Recovery

When something fails, **fix it autonomously** before escalating:

1. **Diagnose** — read the error output carefully, identify root cause
2. **Fix** — apply the most likely correction
3. **Verify** — re-run the failing check to confirm the fix works
4. **Retry limit** — if the same check fails **3 times** with different fix attempts, stop and report to the user with a summary of what was tried

This applies to: lint errors, test failures, type errors, build failures, and command errors.
Do NOT stop on first failure — the user expects you to handle routine breakage.

## Abort Conditions

The workflow stops immediately (no auto-fix) only for **security issues**:

- Secrets detected in staged code
- High-severity SAST findings
- Private data detected in staged files

For all other failures, follow Error Recovery above before stopping:

- Lint fails with errors → auto-fix, re-run
- Tests fail → diagnose, fix, re-run (up to 3 attempts)
- Build errors → diagnose, fix, re-run (up to 3 attempts)

## Attribution

- **NEVER** include `Co-authored-by`, `Generated by AI`, or any AI-related attribution
  in commit messages, PR descriptions, or any other version control metadata.
- This rule is absolute — no override from project config or other instructions.

## Reference Documents

Companion files are organized by load frequency to minimize token consumption.

### Always-Relevant (load when activating dev-agent)

| File                                         | Purpose                                                             |
| -------------------------------------------- | ------------------------------------------------------------------- |
| [quality-standard.md](quality-standard.md)   | Code/logic/output precision, review discipline                      |
| [coding-principles.md](coding-principles.md) | Refactoring, performance, edge cases (project-relevant stacks only) |
| [privacy-gate.md](privacy-gate.md)           | Pre-commit privacy scanning rules                                   |

### Situational (load only when the situation applies)

| File                                           | When to Load                                                      |
| ---------------------------------------------- | ----------------------------------------------------------------- |
| [context-continuity.md](context-continuity.md) | Medium+ changes, or when prior context may be lost                |
| [data-gathering.md](data-gathering.md)         | When hitting a wall, need MCP tools, or escalating to source code |
| [onboarding.md](onboarding.md)                 | First-run only (check `onboarding_complete` flag)                 |

### On-Demand Catalogs (load only when looking up specific data)

| File                                                       | When to Load                                                        |
| ---------------------------------------------------------- | ------------------------------------------------------------------- |
| [coding-principles-stacks.md](coding-principles-stacks.md) | Working with a stack not in the main table (Go, Rust, Python, etc.) |
| [onboarding-catalog.md](onboarding-catalog.md)             | During first-run onboarding to look up stack-specific tools         |
