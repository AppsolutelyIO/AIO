# First-Run Onboarding

When the dev-agent skill is activated for the first time in a project, help the
user set up the best tooling for their tech stack.

## How to Detect First Run

Check memory files for an `onboarding_complete` flag for the current project.
If absent, this is a first run — proceed with onboarding.

After onboarding completes (whether the user accepts or declines recommendations),
save `onboarding_complete: true` to the project memory file so it does not repeat.

## Onboarding Procedure

1. **Detect tech stack** — read manifest files and directory structure (see
   [Tech Stack Detection](#tech-stack-detection) below)
2. **Inventory existing tools** — check `.mcp.json`, `.claude/settings.json`,
   `.agents/skills/` to see what is already installed
3. **Generate recommendations** — start with Core Tools, then add stack-specific
   and conditional items. Filter out already-installed items
4. **Present to user** — show a concise list grouped by category (MCP servers,
   Skills), with one-line descriptions. Let the user pick which to install
5. **Install selected items** — install autonomously, then confirm what was set up

## Recommendation Catalog

### Core Tools (all projects)

These are recommended for every project regardless of tech stack.

**MCP Servers**

| Name | Description |
|------|-------------|
| `context7` | Live documentation lookup for any library |
| `@modelcontextprotocol/server-sequential-thinking` | Step-by-step reasoning for complex problems |
| `@modelcontextprotocol/server-memory` | Persistent knowledge graph memory across sessions |
| `@modelcontextprotocol/server-github` | GitHub issues, PRs, CI/CD, commit analysis |
| `@anthropic/brave-search-mcp` | Web search for up-to-date information |

**Skills**

| Name | Description |
|------|-------------|
| `dev-agent` | Full-cycle development workflow orchestrator (this skill) |
| `conventional-commits` | Atomic commits with conventional commit messages |
| `plankton-code-quality` | Auto-formatting, linting, complexity checks |
| `ghost-scan-secrets` | Pre-commit secrets scanning |
| `ghost-scan-code` | SAST code vulnerability scanning |
| `ghost-scan-deps` | Dependency vulnerability scanning (SCA) |
| `systematic-debugging` | Structured debugging before proposing fixes |
| `code-review-quality` | Context-driven code review |
| `pr-review` | GitHub PR review with scope validation |
| `test-automation-strategy` | Test pyramid design and CI/CD integration |
| `find-skills` | Discover and install new skills on demand |
| `api-documentation` | OpenAPI docs, ADRs, ERD diagrams |

### Stack-Specific & Conditional Tools

For stack-specific recommendations, conditional dependency recommendations,
and CI/CD skills, see [onboarding-catalog.md](onboarding-catalog.md).

Load that file during onboarding to look up what to recommend based on the
detected tech stack.

## Presentation Format

When presenting recommendations, use this format:

```
Based on your tech stack (Laravel 12, Filament v5, Vue 3, Tailwind v4, Livewire 4),
here are recommended tools not yet installed:

**MCP Servers**
- `context7` — live documentation lookup for any library
- `browser-tools-mcp` — browser console/network/screenshot access

**Skills**
- `filament-development` — Filament resource and panel development
- ...

Would you like me to install all of these, or pick specific ones?
```

## Tech Stack Detection

How to identify the project's stack from files in the project root:

| File | Stack |
|---|---|
| `composer.json` | PHP / Laravel |
| `go.mod` | Golang |
| `Cargo.toml` | Rust |
| `tsconfig.json` | TypeScript |
| `pubspec.yaml` | Flutter / Dart |
| `requirements.txt` / `pyproject.toml` / `Pipfile` | Python |
| `Gemfile` | Ruby / Rails |
| `pom.xml` / `build.gradle` / `build.gradle.kts` | Java / Kotlin / Spring |
| `*.csproj` / `*.sln` | .NET / C# |
| `Cargo.toml` with `tauri` / `package.json` with `@tauri-apps/cli` | Tauri |
| `deno.json` / `deno.jsonc` | Deno |
| `bun.lockb` / `bunfig.toml` | Bun |
| `package.json` with `vue` | Vue |
| `package.json` with `nuxt` | Nuxt |
| `package.json` with `react` | React |
| `package.json` with `next` | Next.js |
| `package.json` with `@remix-run/react` | Remix |
| `package.json` with `@nestjs/core` | NestJS |
| `package.json` with `@adonisjs/core` | AdonisJS |
| `package.json` with `hono` | Hono |
| `package.json` with `svelte` | Svelte / SvelteKit |
| `package.json` with `@angular/core` | Angular |
| `package.json` with `react-native` | React Native |
| `package.json` with `@remotion/*` | Remotion |

A project can match multiple stacks (e.g., Laravel + Vue + TypeScript + Tailwind).
Recommend the union of all matching catalogs, deduplicated.
