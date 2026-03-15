# Data Gathering and Tool Discovery

## Tool Discovery — Never Ask the User for What a Tool Can Provide

Before asking the user to copy-paste errors, check logs, or provide runtime data,
**always check if an MCP tool or Skill can do it for you.** Asking the user is the
last resort, not the first instinct.

### Auto-Discovery Protocol

At the start of every task and whenever you hit a wall:

1. **Inventory available MCP tools** — check what MCP servers are configured (`.mcp.json`, MCP settings). List their capabilities mentally
2. **Inventory installed Skills** — check `.agents/skills/` for existing skills that may help
3. **Match the need to the tool** — before asking the user, check if an MCP tool can provide the data:
   - Need browser errors? → browser-logs tool
   - Need application logs? → log-reading tool
   - Need database data? → database query / REPL tool
   - Need to run code in project context? → REPL tool (tinker, rails console, python shell, etc.)
   - Need route info? → route listing tool
   - Need config values? → config inspection tool
   - Need documentation? → doc search tool

### When a Needed Tool Doesn't Exist

If you identify that an MCP server or Skill would solve the problem but isn't installed:

1. **Search for it** — use `npx skills find [query]` to search the skill ecosystem, or search for known MCP servers
2. **Skills** — install directly (low risk, reversible). Mention what you installed in your next status update
3. **MCP servers** — install directly if the configuration is straightforward. For servers requiring API keys or complex setup, inform the user

### Skill Discovery and Installation

| Editor | Find Skills | Install Skill (project) | Install Skill (global) |
|---|---|---|---|
| **Claude Code** | `npx skills find [query]` | `npx skills add <owner/repo@skill>` | `npx skills add <owner/repo@skill> -g -y` |
| **Cursor** | `npx skills find [query]` | `npx skills add <owner/repo@skill>` | `npx skills add <owner/repo@skill> -g -y` |
| **VS Code / Other** | `npx skills find [query]` | `npx skills add <owner/repo@skill>` | `npx skills add <owner/repo@skill> -g -y` |

## Application Availability Diagnosis

When browser automation (Playwright MCP) or manual browsing fails to load the
application, run this diagnostic sequence before asking the user:

### Step 1 — Backend Health (curl)

```bash
curl -s -o /dev/null -w "%{http_code} %{time_total}s" http://<app-url>
```

- **2xx / 3xx** → backend is healthy, move to Step 2
- **4xx / 5xx** → backend issue. Check logs (`last-error`, `read-log-entries`),
  inspect `storage/logs/laravel.log`, or run `php artisan` to verify the app boots
- **Connection refused / timeout** → web server not running. Check if Docker / Sail /
  `php artisan serve` is up. Suggest `composer run dev` or equivalent

### Step 2 — Frontend Health (restart in current project)

A Vite process detected via `pgrep -f vite` may belong to another project.
**Do not trust process checks alone.** Instead, restart Vite in the current
project to be certain:

1. **Kill any process on the expected port** and remove stale state:
   ```bash
   lsof -ti:<port> | xargs kill -9 2>/dev/null
   rm -f public/hot
   ```
2. **Restart with one of these approaches** (pick based on context):
   - `npm run dev` — start Vite dev server (development, HMR)
   - `npm run build` — build static assets (production-like, no hot file)
3. **Inspect the output** — wait a few seconds, then read the log. Look for:
   - Port conflicts → kill the occupying process and retry
   - TypeScript / Sass / build errors → fix them before proceeding
   - "ready in Xms" → frontend is healthy
4. **Verify the page loads** — retry the browser navigation. If it still fails,
   the problem is not frontend

### Step 3 — Network / DNS

If backend and frontend are both healthy but the browser still cannot load:

1. **Verify DNS resolution** — `ping -c 1 <app-url>` or check `/etc/hosts`
2. **Try alternative URLs** — `http://localhost:<port>`, `http://127.0.0.1:<port>`,
   or the Docker-internal IP
3. **Timeout threshold** — if a page does not load within 15–30 seconds, conclude
   it is a network/host issue and inform the user with the diagnostic findings

## Playwright Browser Connection (CDP)

Playwright MCP connects to Chrome via CDP (Chrome DevTools Protocol) on port 9222.

### Connection Flow

```
Need browser automation
  └─ pgrep -f "remote-debugging-port=9222" → found? → use browser_navigate directly
  └─ not found → launch Chrome with CDP (see below) → verify → use browser_navigate
```

### Launching Chrome with CDP

**Critical**: If the user already has a Chrome instance running, passing
`--remote-debugging-port=9222` alone will NOT work — Chrome joins the existing
session and ignores the flag. You MUST use a separate `--user-data-dir`:

```bash
"/Applications/Google Chrome.app/Contents/MacOS/Google Chrome" \
  --remote-debugging-port=9222 \
  --user-data-dir=/tmp/chrome-cdp-profile \
  --no-first-run &>/dev/null &
```

### Verification

Wait 2–3 seconds, then confirm CDP is listening:

```bash
curl -s http://localhost:9222/json/version
```

A valid JSON response with `webSocketDebuggerUrl` means CDP is ready.

### Page Load & Screenshots

**Never trust accessibility snapshots alone** — page builder content (custom blocks,
carousels, dynamic sections) is often invisible to `browser_snapshot`. Always take
a screenshot to verify what is actually rendered.

Before taking a screenshot or reading the snapshot:

1. **Wait for full load** — use `browser_navigate` and let the page finish loading.
   Do not navigate to the next page until you have captured the current one.
2. **One page at a time** — when browsing multiple pages sequentially, complete all
   capture (snapshot + screenshot) for the current page before navigating away.
   Navigating too fast causes content from different pages to mix up.
3. **Use full-page screenshots** for analysis — `fullPage: true` captures below-the-fold
   content that viewport screenshots miss.
4. **Cross-reference** — if the snapshot shows only header + footer but the screenshot
   shows rich content, trust the screenshot. The page builder renders content that
   is not accessible to the DOM snapshot tool.

### Error Reporting

When console errors, network failures, or page errors occur during browsing:

1. **Report full details immediately** — include the exact error message, stack trace,
   URL, HTTP status code, and any relevant request/response info. Do not summarize
   as "1 error occurred" — the user needs the specifics to diagnose quickly.
2. **Read console log files** — `browser_navigate` returns console log file paths
   (e.g., `.playwright-mcp/console-*.log`). Read these files and report their
   full content, not just the count.
3. **Network errors** — use `browser_network_requests` or `browser_console_messages`
   to capture failed requests, 4xx/5xx responses, and CORS issues with full URLs.
4. **Distinguish severity** — clearly separate JS runtime errors, resource loading
   failures, mixed content warnings, and application-level errors. Prioritize
   errors over warnings in the report.

### URL Resolution

Always use `mcp__laravel-boost__get-absolute-url` (or the equivalent tool) to get
the correct scheme, domain, and port. Never hardcode URLs.

### Important: Do Not Guess Fixes

When the user corrects a diagnosis (e.g., "SSL is not the problem"), **revert
the attempted fix immediately**. Do not keep speculative changes in the codebase.
Only commit changes that are confirmed to solve the actual problem.

### Decision Flow

```
Page fails to load
  └─ curl backend → fails? → fix server / suggest startup command
  └─ curl backend → ok → restart Vite in current project
      └─ Vite output has errors? → fix build errors
      └─ Vite starts clean? → retry browser → still fails? → network/DNS → ask user
```

## Investigation Methodology: Root Cause → Cross-Validate → Conclude

When diagnosing any issue, follow this strict sequence. Do not skip steps.

### 1. Exhaust All Means to Find the Root Cause

If analysis yields multiple possible causes (A, B, C), do not stop and list them.
Investigate each one until you identify **the** definitive cause. Use every tool
available — Docker exec, browser, logs, database queries, REPL, code tracing.

If one path is blocked (e.g., database unreachable from host), find another path
(e.g., `docker exec` into the container). "I can't connect" is never a conclusion
— it's a reason to try a different approach.

### 2. Cross-Validate the Conclusion

Finding the root cause is not enough. You must **prove** it by fixing the suspected
cause and verifying the problem disappears:

1. **Apply the fix** — change the one thing you believe is the root cause
2. **Re-test** — reproduce the original scenario (reload page, re-run test, etc.)
3. **Confirm** — if the problem is gone, the conclusion is confirmed
4. **If still broken** — your diagnosis was wrong. Revert and investigate further

Without cross-validation, a conclusion is just a hypothesis.

### 3. Report Only the Confirmed Conclusion

After cross-validation succeeds, report the single confirmed root cause.
Do not list "possible causes" — the user already knows the possibilities.
They need the answer.

### Example

> **Bad**: "/accessories returns 404. Possible causes: page doesn't exist,
> page is inactive, or page is unpublished. Please check the database."
>
> **Good**: "/accessories returns 404 because the page (ID=21) has `status=0`
> (Inactive). Changed to Active and confirmed the page now loads correctly."

## Data Gathering Priority Ladder

### Priority 1 — Use Available MCP Tools

Check installed MCP servers first. Common capabilities by category:

| Need | Tool Type | Examples |
|------|-----------|----------|
| Browser errors | Browser logs | `browser-logs` |
| Application logs | Log reader | `read-log-entries`, framework log tools |
| Last error / stack trace | Error inspector | `last-error` |
| Route inspection | Route lister | `list-routes`, framework route tools |
| Config / env values | Config reader | `get-config`, framework config tools |
| Run code in project context | REPL | `tinker` (PHP), `rails console` (Ruby), `python manage.py shell` (Django), `node --eval` (JS) |
| Database read | Query tool | `database-query`, `prisma-query`, DB-specific MCP tools |
| Database schema | Schema inspector | `database-schema`, migration files, ORM schema dumps |
| Documentation | Doc search | `search-docs`, framework-specific doc tools |

### Priority 2 — Verify Through Code

1. **REPL** — execute code in the project's runtime to verify behavior directly
2. **Run the relevant test** — a passing test proves the operation works
3. **Read the code path** — trace the call chain (controller → service → repository, handler → middleware → resolver, etc.)
4. **Check side effects** — observers, event listeners, middleware, hooks, signals — anything that modifies data after the primary operation

### Priority 3 — Direct Database / State Inspection

1. **MCP database tools** — preferred for read-only queries
2. **CLI clients** — `psql`, `mysql`, `mongosh`, `redis-cli`, `sqlite3`, etc.
3. **ORM tools** — migration status, schema dumps, model inspection commands

### Priority 4 — Manual Connection (Fallback)

Read environment config (`.env`, `config/*.yml`, etc.) for credentials and connect via CLI. Never modify data directly unless explicitly asked.

### Priority 5 — Ask the User

Explain what you need, why you need it, and be specific about what to check.

## Escalation — Source Code Deep Dive

### Trigger Conditions

Activate when ANY of these occur:

- Same test/build fails **3+ times** after different fix attempts
- Syntax/type errors persist despite following documentation
- API behavior does not match docs or training data
- A method signature or interface cannot be verified from project code

### Procedure

1. **Read local dependencies first** — most package managers store source locally (e.g., `vendor/` for PHP, `node_modules/` for JS/TS, `site-packages/` for Python, `$GOMODCACHE` for Go, `~/.cargo/registry/src/` for Rust)
2. **If not available locally** — create temp workspace → `mkdir -p /tmp/source-dive`, download at the exact version from lock files
3. Analyze actual interfaces, signatures, and wiring
4. Apply findings based on real source, not docs

> **Source code is the single source of truth.** When in doubt, read the implementation.

### Temp Workspace Lifecycle

- **Do NOT delete the temp workspace** after use — the downloaded source may be needed again in the same session. Avoid redundant downloads
- Use `/tmp/source-dive` on Linux, or a persistent path like `~/.cache/source-dive` on macOS (where `/tmp` is cleared on reboot)
- Only delete if the user explicitly requests cleanup
- The temp workspace directory is git-ignored and will not enter version control
