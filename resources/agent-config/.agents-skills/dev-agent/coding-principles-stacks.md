# Per-Stack Coding Standards Reference

Load this file only when working with an unfamiliar stack or when the project
spans multiple language ecosystems. For single-stack projects, the main
`coding-principles.md` file contains all you need.

## Languages

| Stack | Official Standard | Key Conventions |
|-------|-------------------|-----------------|
| **PHP** | PSR-12 + Laravel conventions | `camelCase` methods, `PascalCase` classes, `snake_case` DB columns/config keys, `$camelCase` variables |
| **JavaScript** | MDN + ESLint | `camelCase` variables/functions, `PascalCase` classes, `UPPER_SNAKE_CASE` constants, strict equality (`===`) |
| **TypeScript** | TypeScript Handbook + ESLint | `camelCase` variables/functions, `PascalCase` types/interfaces/classes, strict mode, explicit types at boundaries |
| **Go** | Effective Go + `gofmt` | `CamelCase` exports, `camelCase` unexported, short receiver names, error returns not exceptions, `gofmt` formatting |
| **Rust** | Rust API Guidelines + `rustfmt` | `snake_case` functions/variables, `PascalCase` types/traits, `SCREAMING_SNAKE_CASE` constants, `rustfmt` formatting |
| **Dart** | Effective Dart + `dart format` | `lowerCamelCase` variables/functions, `UpperCamelCase` classes/enums, `lowercase_with_underscores` packages/files, `dart format` |
| **Python** | PEP 8 + Black/Ruff | `snake_case` functions/variables, `PascalCase` classes, `UPPER_SNAKE_CASE` constants, type hints (3.10+) |
| **Ruby** | Ruby Style Guide + RuboCop | `snake_case` methods/variables, `PascalCase` classes/modules, `SCREAMING_SNAKE_CASE` constants |
| **Java** | Google Java Style Guide | `camelCase` methods/variables, `PascalCase` classes, `UPPER_SNAKE_CASE` constants, Javadoc on public APIs |
| **Kotlin** | Kotlin Coding Conventions | `camelCase` methods/variables, `PascalCase` classes, `UPPER_SNAKE_CASE` constants, data classes, nullable types |
| **Swift** | Swift API Design Guidelines | `camelCase` methods/variables, `PascalCase` types/protocols, no prefixes, trailing closures, `guard` for early exit |
| **C#** | .NET Naming Guidelines | `PascalCase` methods/properties/classes, `camelCase` local variables/parameters, `I` prefix for interfaces |

## Frontend Frameworks

| Stack | Official Standard | Key Conventions |
|-------|-------------------|-----------------|
| **React** | React official docs + ESLint plugin | `PascalCase` components, `camelCase` props, hooks start with `use`, prefer function components, composition over inheritance |
| **Next.js** | Next.js docs + Vercel best practices | App Router (`app/`), Server Components by default, `'use client'` only when needed, `loading.tsx`/`error.tsx` conventions, file-based routing |
| **Vue** | Vue Style Guide (Essential + Strongly Recommended) | `PascalCase` components, `camelCase` props in JS / `kebab-case` in templates, `v-` directive style, SFC `<script setup>` |
| **Nuxt** | Nuxt official docs | Auto-imports, `pages/` file routing, `composables/` for shared logic, `server/` for API routes, `useFetch`/`useAsyncData` for data fetching |
| **Svelte** | Svelte docs | `PascalCase` components, `$:` reactive declarations, `{#each}` blocks, `+page.svelte` routing (SvelteKit), `$props()` rune (Svelte 5) |
| **Remix** | Remix docs (React Router v7) | Nested routes, `loader`/`action` server functions, `useLoaderData` hook, progressive enhancement, form-based mutations |
| **Angular** | Angular Style Guide | `PascalCase` classes, `camelCase` methods, `kebab-case` selectors, one component per file, barrel exports, dependency injection |
| **CSS/Tailwind** | Tailwind official docs | Utility-first, avoid `@apply` unless extracting components, mobile-first responsive (`sm:` → `lg:`), design tokens via config |

## Backend Frameworks

| Stack | Official Standard | Key Conventions |
|-------|-------------------|-----------------|
| **Laravel** | Laravel official docs | Eloquent conventions, Form Requests, Resource controllers, `route()` helper, `config()` not `env()` |
| **Express/Fastify** | Node.js best practices | `camelCase` everywhere, middleware chaining, async error handling, env via config module |
| **Django** | Django coding style (PEP 8 based) | `snake_case` views/models/functions, `PascalCase` classes, model `Meta` class, `urls.py` routing, ORM querysets |
| **FastAPI** | FastAPI docs + Pydantic | `snake_case` endpoints/functions, `PascalCase` Pydantic models, type annotations required, async by default |
| **Ruby on Rails** | Rails Guides + RuboCop | `snake_case` methods, `PascalCase` classes, RESTful resources, convention over configuration, `ActiveRecord` patterns |
| **Spring Boot** | Spring official guides | `camelCase` methods, `PascalCase` classes, `@Annotation` config, dependency injection, `application.yml` properties |
| **Gin/Echo (Go)** | Effective Go + framework docs | `CamelCase` exports, middleware chaining, `c.JSON()` responses, struct binding for request validation |
| **Actix/Axum (Rust)** | Rust API Guidelines + framework docs | `snake_case` handlers, extractors for request data, `impl IntoResponse`, tower middleware (Axum), `Result<T, E>` error handling |
| **NestJS** | NestJS official docs | `PascalCase` classes, `camelCase` methods, decorators (`@Controller`, `@Injectable`), modules/providers/controllers pattern, DTO validation with `class-validator` |
| **AdonisJS** | AdonisJS official docs | `camelCase` methods, `PascalCase` classes, MVC pattern, Lucid ORM (similar to Laravel Eloquent), Edge templates |
| **Hono** | Hono official docs | `camelCase` handlers, middleware chaining, `c.json()` responses, type-safe routes with `zValidator`, multi-runtime (Workers/Deno/Bun) |
| **Tauri (Rust)** | Tauri v2 docs + Rust API Guidelines | `snake_case` commands, `#[tauri::command]` macro, `AppHandle`/`State` extractors, IPC via `invoke()`, permission-based security model |

## Mobile Frameworks

| Stack | Official Standard | Key Conventions |
|-------|-------------------|-----------------|
| **React Native** | React Native docs + React conventions | Same as React (`PascalCase` components, hooks), `StyleSheet.create()`, platform-specific files (`.ios.tsx`/`.android.tsx`) |
| **Flutter** | Effective Dart + Flutter Style Guide | `UpperCamelCase` widgets/classes, `lowerCamelCase` variables, widget composition, `const` constructors, `StatelessWidget` by default |
| **SwiftUI** | Swift API Design Guidelines | `PascalCase` views/structs, `camelCase` properties, `@State`/`@Binding` property wrappers, declarative view builders |
| **Kotlin Android** | Android Kotlin Style Guide | `camelCase` functions, `PascalCase` classes, Jetpack Compose `@Composable` functions as `PascalCase`, `ViewModel` pattern |
