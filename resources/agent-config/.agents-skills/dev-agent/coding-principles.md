# Coding Principles

These principles govern how code is written during all workflow steps.

## Follow Official Standards

All code must follow the **official conventions and idioms** of the tech stack in use.
Do not invent custom styles — write code the way the framework/language community expects.

When unsure about conventions, use `search-docs` MCP tool or read official docs before writing.

### Per-Stack Standards (current project)

| Stack | Official Standard | Key Conventions |
|-------|-------------------|-----------------|
| **PHP** | PSR-12 + Laravel conventions | `camelCase` methods, `PascalCase` classes, `snake_case` DB columns/config keys, `$camelCase` variables |
| **JavaScript** | MDN + ESLint | `camelCase` variables/functions, `PascalCase` classes, `UPPER_SNAKE_CASE` constants, strict equality (`===`) |
| **TypeScript** | TypeScript Handbook + ESLint | `camelCase` variables/functions, `PascalCase` types/interfaces/classes, strict mode, explicit types at boundaries |
| **Vue** | Vue Style Guide (Essential + Strongly Recommended) | `PascalCase` components, `camelCase` props in JS / `kebab-case` in templates, `v-` directive style, SFC `<script setup>` |
| **Laravel** | Laravel official docs | Eloquent conventions, Form Requests, Resource controllers, `route()` helper, `config()` not `env()` |
| **CSS/Tailwind** | Tailwind official docs | Utility-first, avoid `@apply` unless extracting components, mobile-first responsive (`sm:` → `lg:`), design tokens via config |

> For other stacks (Go, Rust, Python, Swift, React Native, etc.), see
> [coding-principles-stacks.md](coding-principles-stacks.md).

### General Rules

- **Naming must match the stack's idiom** — Go uses `MixedCaps` not `snake_case` for exports; Rust uses `snake_case` not `camelCase` for functions. Mixing idioms is a bug.
- **Use the stack's built-in tooling** — `gofmt` for Go, `rustfmt` for Rust, Pint for Laravel, Prettier/ESLint for TS/Vue. Never fight the formatter.
- **Follow the framework's way** — if Laravel provides `Form Requests`, use them (don't validate inline). If Go provides `error` returns, use them (don't panic). If Rust provides `Result<T, E>`, use it (don't unwrap in library code).
- **When the project deviates from official standards** — follow the project's existing convention, not the official one. Consistency within the project trumps external standards. But flag the deviation as a potential cleanup task.

## Test/Debug Routes

When creating temporary test or debug routes:

1. **Flat paths only** — use a single-segment path like `/test-session`, never nested paths like `/_test/session`
2. **Return JSON** — do not create Blade views or templates for test routes; return `response()->json()` directly
3. **Minimal structure** — no `prefix()`, `group()`, `name()`, or other routing wrappers. Just a plain `Route::get()`
4. **Never commit** — test/debug routes are temporary. Do not commit them unless the user explicitly asks

## Proactive Refactoring

While exploring the codebase for a task, actively look for improvement opportunities:

1. **Detect duplication** — if two or more methods/blocks do similar things, extract a shared reusable method before proceeding. Do this as a separate commit before the feature work
2. **Detect inconsistency** — if similar logic is implemented differently in different places, unify them into one canonical approach
3. **Detect dead code** — unused imports, unreachable branches, commented-out code: remove them in a dedicated cleanup commit
4. **When there is a conflict or ambiguity** — assess the scope:
   - **Code-level** (e.g., two similar utility methods, inconsistent naming in sibling files) → pick the better option and proceed. Mention the choice briefly in the commit message
   - **Architecture-level** (e.g., two contradictory design patterns, conflicting service interfaces, ambiguous data model ownership) → **stop and ask the user to decide**. Present the options clearly with pros/cons
5. **Scope rule** — refactoring must be directly related to the current task. Don't refactor unrelated code just because you noticed it; note it for a future task instead

## Optimal Solutions, Not Quick Fixes

Write code for the long term, not just to pass the current test:

1. **Query outside loops** — never put database queries, API calls, or cache reads inside a loop. Batch-fetch before the loop, then iterate over the result
2. **Minimize cache usage** — cache only what is expensive to compute and frequently read. Don't cache cheap queries or rarely accessed data. Every cache entry is a future invalidation problem
3. **Prefer database-level operations** — use `UPDATE ... WHERE`, `upsert()`, `increment()`, aggregate queries instead of loading models into PHP to manipulate them one by one
4. **Choose the right data structure** — use `Collection::keyBy()` for lookups instead of nested loops; use sets for membership checks; use indexed arrays for ordered data
5. **Think about scale** — code that works for 100 rows may fail at 100,000. Ask: "What happens when this table has a million rows?"
6. **Avoid premature abstraction, but recognize mature patterns** — if the same 3-line pattern appears in 3+ places, it's time for an abstraction. If it appears once, leave it inline
7. **Favor declarative over imperative** — use query scopes, Eloquent relationships, validation rules, and framework conventions over hand-written procedural logic

## Incremental Implementation

Never change everything at once. Follow this rhythm:

1. **One logical change at a time** — modify one file or one concern, then verify it works before moving on
2. **Run tests after each step** — don't accumulate 10 file changes and then discover the second one broke everything
3. **Commit at each green state** — if tests pass after a meaningful change, commit it. This creates save points to fall back to
4. **Order of operations**:
   - Interfaces/contracts first
   - Implementation second
   - Tests third
   - Wiring (routes, config, providers) last
5. **If a step fails, fix it before proceeding** — never stack changes on top of a broken state

## Performance Awareness

Think about performance at write time, not as an afterthought:

1. **N+1 queries** — any loop that touches a relationship must use eager loading (`with()`, `load()`)
2. **Missing indexes** — if adding a `where` clause, `orderBy`, or `unique` constraint, verify the column is indexed
3. **Large table operations** — use chunking (`chunk()`, `chunkById()`, `lazy()`) for bulk reads; use `upsert()` or batch inserts for bulk writes
4. **Cache invalidation** — if the change modifies data that is cached (response cache, query cache, application cache), ensure the cache is properly cleared
5. **Unnecessary eager loading** — don't load relationships that aren't used; check what the view/response actually needs

## OWASP Security

All code must defend against the [OWASP Top 10](https://owasp.org/www-project-top-ten/) vulnerabilities:

1. **Broken Access Control** — always authorize (gates, policies, `$this->authorize()`) before exposing data or actions. Never rely on hidden URLs or front-end checks alone
2. **Cryptographic Failures** — use `Hash::make()` / `bcrypt()` for passwords, `encrypt()` for sensitive data, HTTPS for transport. Never store secrets in plaintext or logs
3. **Injection** — use parameterized queries (Eloquent / query builder), never concatenate user input into SQL, shell commands (`Process::run()`), or HTML. Blade `{{ }}` auto-escapes; never use `{!! !!}` with user input
4. **Insecure Design** — validate all inputs via Form Requests, apply rate limiting on sensitive endpoints, implement proper CSRF protection (Laravel handles this by default)
5. **Security Misconfiguration** — never expose debug info in production (`APP_DEBUG=false`), keep `.env` out of version control, set restrictive CORS and CSP headers
6. **Vulnerable Components** — keep dependencies updated, run `composer audit` and `npm audit` regularly, review security advisories before upgrading
7. **Authentication Failures** — use Laravel Fortify/Sanctum for auth, enforce strong passwords, implement account lockout after failed attempts, use MFA where appropriate
8. **Data Integrity Failures** — validate and sanitize all external input, verify webhooks with signatures, use signed URLs for sensitive operations
9. **Logging & Monitoring Failures** — log auth events, access control failures, and input validation failures. Never log sensitive data (passwords, tokens, PII)
10. **SSRF** — validate and whitelist URLs before making server-side HTTP requests, never pass raw user input to `Http::get()` or `file_get_contents()`

### Laravel-Specific Security Checklist

- Mass assignment: always define `$fillable` or `$guarded` on models
- File uploads: validate MIME types and extensions, store outside web root, use randomized filenames
- Session: use `httpOnly`, `secure`, `sameSite` cookie attributes (Laravel defaults handle this)
- Headers: CSP, X-Frame-Options, X-Content-Type-Options via SecurityHeaders middleware
- API: authenticate with Sanctum tokens, validate scopes, rate-limit per user

## Edge Cases and Concurrency

Think beyond the happy path:

1. **Empty state** — what happens when the collection is empty, the relationship is null, or the string is blank?
2. **Boundary values** — zero, negative numbers, maximum integer, empty arrays, extremely long strings
3. **Race conditions** — concurrent form submissions, double-click on buttons, overlapping queue jobs modifying the same record
4. **Pessimistic locking** — use `lockForUpdate()` when two processes might write to the same row simultaneously
5. **Idempotency** — can the same job/action run twice without causing harm? If not, add guards
6. **Timeouts and failures** — external API calls, queue jobs, file uploads: what happens when they time out or return errors?
7. **Write tests for these** — edge case tests are more valuable than another happy-path test
