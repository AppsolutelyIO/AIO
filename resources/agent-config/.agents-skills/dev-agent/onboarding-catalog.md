# Onboarding Tool Catalog — Stack-Specific & Conditional

Load this file only during first-run onboarding to look up stack-specific
recommendations. Not needed for daily development work.

## Stack-Specific Tools

Each section lists **only tools unique to that stack**. Core tools from
`onboarding.md` are always included and not repeated here.

### Laravel / PHP

| Type | Name | Description |
|------|------|-------------|
| MCP | `laravel/boost` | Database, Artisan, logs, tinker, docs search — the essential Laravel MCP |
| MCP | `browser-tools-mcp` | Browser console, network, screenshots for frontend debugging |

### Filament

| Type | Name | Description |
|------|------|-------------|
| Skill | `filament-development` | Filament panel, resource, and widget development |

### Golang

Detect: `go.mod` in project root. No additional tools beyond core.

### TypeScript / Node.js

Detect: `tsconfig.json` or `package.json` with `typescript` dependency.

| Type | Name | Description |
|------|------|-------------|
| MCP | `browser-tools-mcp` | Browser console, network tab, screenshots (if web project) |

### Rust

Detect: `Cargo.toml` in project root.

| Type | Name | Description |
|------|------|-------------|
| MCP | `cargo-mcp` (`camshaft/cargo-mcp`) | Cargo build, test, check, fmt integration; crates.io lookups |

### Tauri

Detect: `tauri` in `Cargo.toml` dependencies, or `@tauri-apps/cli` in `package.json`.

| Type | Name | Description |
|------|------|-------------|
| MCP | `@hypothesi/tauri-mcp-server` | Build, test, debug Tauri v2 apps — screenshots, console logs, window state, IPC inspection |
| MCP | `tauri-docs` (`Michael-Obele/tauri-docs`) | Official Tauri documentation via MCP (SSE transport) |
| MCP | `cargo-mcp` (`camshaft/cargo-mcp`) | Cargo build, test, check, fmt integration |
| Skill | `dchuk/claude-code-tauri-skills` | Comprehensive Tauri v2 skills (39+) — setup, security, IPC, plugins, mobile, distribution |
| Skill | `frontend-design` | Frontend UI generation (for the webview layer) |

**Tauri Rust plugins** (add to `Cargo.toml` when building MCP-enabled Tauri apps):
- `tauri-plugin-mcp` — enable MCP tools within Tauri apps for AI debugging
- `tauri-plugin-mcp-gui` — GUI-specific MCP: screenshots, DOM access, JS execution
- `tauri-plugin-mcp-bridge` — expose Tauri internals (IPC, window state, events) to MCP servers

### React / Next.js

Detect: `react` or `next` in `package.json` dependencies.

| Type | Name | Description |
|------|------|-------------|
| MCP | `browser-tools-mcp` | Browser console, network tab, screenshots |
| Skill | `vercel-react-best-practices` | React/Next.js performance optimization from Vercel Engineering |
| Skill | `frontend-design` | Production-grade frontend UI generation |
| Skill | `ui-ux-pro-max` | UI/UX design intelligence with style/palette libraries |
| Skill | `web-design-guidelines` | Web Interface Guidelines compliance review |

### Remix

Detect: `@remix-run/react` or `remix` in `package.json` dependencies.

| Type | Name | Description |
|------|------|-------------|
| MCP | `browser-tools-mcp` | Browser console, network tab, screenshots |
| Skill | `vercel-react-best-practices` | React performance patterns (Remix uses React) |
| Skill | `frontend-design` | Production-grade frontend UI generation |

### Vue / Nuxt

Detect: `vue` or `nuxt` in `package.json` dependencies.

| Type | Name | Description |
|------|------|-------------|
| MCP | `browser-tools-mcp` | Browser console, network tab, screenshots |
| MCP | `vite-plugin-vue-mcp` | Vue component tree, state, routes, Pinia store inspection via Vite |
| Skill | `frontend-design` | Production-grade frontend UI generation |
| Skill | `ui-ux-pro-max` | UI/UX design intelligence with style/palette libraries |
| Skill | `web-design-guidelines` | Web Interface Guidelines compliance review |

### Svelte / SvelteKit

Detect: `svelte` in `package.json` dependencies.

| Type | Name | Description |
|------|------|-------------|
| MCP | `browser-tools-mcp` | Browser console, network tab, screenshots |
| Skill | `frontend-design` | Production-grade frontend UI generation |
| Skill | `ui-ux-pro-max` | UI/UX design intelligence with style/palette libraries |

### Angular

Detect: `@angular/core` in `package.json` dependencies.

| Type | Name | Description |
|------|------|-------------|
| MCP | `browser-tools-mcp` | Browser console, network tab, screenshots |
| Skill | `frontend-design` | Production-grade frontend UI generation |
| Skill | `ui-ux-pro-max` | UI/UX design intelligence with style/palette libraries |

### React Native

Detect: `react-native` in `package.json` dependencies.

| Type | Name | Description |
|------|------|-------------|
| Skill | `vercel-react-best-practices` | React performance patterns (shared with web React) |
| Skill | `ui-ux-pro-max` | UI/UX design intelligence — includes React Native stack |

### Flutter / Dart

Detect: `pubspec.yaml` in project root.

| Type | Name | Description |
|------|------|-------------|
| Skill | `ui-ux-pro-max` | UI/UX design intelligence — includes Flutter stack |

### NestJS

Detect: `@nestjs/core` in `package.json` dependencies.

| Type | Name | Description |
|------|------|-------------|
| MCP | `@nestjs-mcp/server` | NestJS module for building MCP servers with decorators and DI |

### Hono

Detect: `hono` in `package.json` dependencies.

| Type | Name | Description |
|------|------|-------------|
| MCP | `@hono/mcp` | Build MCP servers on Hono — supports Cloudflare Workers, Deno, Bun |

### Strapi

Detect: `@strapi/strapi` in `package.json` dependencies.

| Type | Name | Description |
|------|------|-------------|
| MCP | `strapi-mcp-server` | Interact with Strapi content — CRUD entries, filtering, media uploads |

### Deno / Bun / AdonisJS / Python / Ruby / Java / .NET

These stacks have no unique tools beyond core. All core tools apply.

Detect files: `deno.json`, `bun.lockb`, `@adonisjs/core`, `requirements.txt`/`pyproject.toml`,
`Gemfile`, `pom.xml`/`build.gradle`, `*.csproj`/`*.sln`.

## Conditional Recommendations

These tools are only recommended when a specific dependency is detected.
This is the **single lookup table** — do not duplicate these in stack sections.

| Dependency | Type | Recommend | Description |
|---|---|---|---|
| `livewire/livewire` | Skill | `livewire-development` | Livewire component development |
| `tailwindcss` | Skill | `tailwindcss-development` | Tailwind CSS styling guidance |
| `laravel/ai` | Skill | `ai-sdk-development` | Laravel AI SDK integration |
| `spatie/laravel-responsecache` | Skill | `responsecache-development` | Response caching |
| `laravel/mcp` | Skill | `mcp-development` | MCP server/tool development |
| `filament/filament` | Skill | `filament-development` | Filament panel development |
| `@remotion/*` | Skill | `remotion-best-practices` | Remotion video creation |
| `vuetify` | MCP | `@vuetify/mcp` | Vuetify v3 component library context |
| `prisma` | MCP | `@prisma/mcp-server` | Database migrations, queries |
| `prisma` / `typeorm` / `drizzle` / `sqlalchemy` / PostgreSQL | MCP | `@modelcontextprotocol/server-postgres` | PostgreSQL query, schema inspection |
| `mongodb` / `mongoose` | MCP | `mongodb-mcp-server` | Query collections, inspect schemas |
| `redis` / `ioredis` / `predis` | MCP | `redis-mcp` | Manage Redis data structures |
| `@supabase/supabase-js` | MCP | `supabase-mcp` | Manage tables, query data, auth, storage |
| `firebase` / `firebase-admin` | MCP | `firebase-mcp` | Manage Firestore, Cloud Functions, auth |
| `@apollo/client` / `graphql` | MCP | `apollo-mcp-server` | GraphQL operations, schema introspection |
| `@trpc/server` | MCP | `trpc-mcp` | Serve tRPC routes as MCP tools |
| gRPC + Go (`protoc`) | MCP | `protoc-gen-go-mcp` | Convert gRPC services to MCP servers |
| `playwright` / `@playwright/test` | MCP | `@anthropic/playwright-mcp` | Browser automation, E2E tests |
| `cypress` | MCP | `cypress-mcp` | Generate Cypress test cases and Page Objects |
| `Dockerfile` / `docker-compose.yml` | MCP | `mcp-server-docker` | Manage containers, images, networks, volumes |
| `*.tf` files | MCP | `terraform-mcp-server` | Registry API, provider docs, workspace management |
| `k8s/` dir or kubeconfig | MCP | `kubernetes-mcp-server` | Manage pods, deployments, namespaces, logs |

## CI/CD & DevOps Skills

| Name | Description |
|------|-------------|
| `create-github-action-workflow-specification` | GitHub Actions workflow specs |
| `release-management` | Versioning and release workflow |
