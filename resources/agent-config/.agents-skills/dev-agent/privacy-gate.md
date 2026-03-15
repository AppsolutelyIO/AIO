# Privacy Gate — Before Every Commit

Before staging or committing ANY file, scan for private information that must
never enter version control. This is a **hard gate** — block the commit until
all violations are resolved.

## Categories of Private Data

| Category | Examples | Common Locations |
|---|---|---|
| **Credentials** | API keys, secrets, tokens, passwords, OAuth client IDs/secrets | `.env`, config files, SKILL.md, CLAUDE.md |
| **Local paths** | `/Users/username/...`, `/home/username/...`, `C:\Users\...` | Config files, scripts, documentation |
| **Usernames** | OS username, database username, Git author in non-commit files | Config, docs, comments |
| **Database connections** | Host, port, database name, credentials | `.env`, config files, MCP configs |
| **MCP/GPT configs** | MCP server configs with local paths, API endpoints | `.mcp.json`, `.cursor/mcp.json` |

## Files That Must NEVER Be Committed

- `.env`, `.env.local`, `.env.*.local`
- `.mcp.json`
- `.claude/settings.local.json`
- `.cursor/mcp.json`
- GPT/AI config files with API keys or endpoints
- Any file containing hardcoded credentials or tokens

**Verify these are in `.gitignore`.** If not, add them before committing.

## Scan Procedure

Before every commit:

1. **Review staged files** — `git diff --staged --name-only`
2. **Pattern scan** — search staged content for:
   - Paths matching `/Users/`, `/home/`, `C:\Users\`
   - Strings matching `key`, `secret`, `token`, `password`, `credential` followed by `=` or `:`
   - IP addresses and port numbers that look like local services
   - Base64-encoded strings longer than 40 characters
3. **Config file check** — verify placeholder values or env var references, not real credentials
4. **Skill files check** — verify no local paths, usernames, or project-specific identifiers

## When Private Data Is Found — Auto-Fix

The agent must **fix violations autonomously**, not just report them:

1. **Auto-replace** — substitute the private data with the appropriate safe alternative:
   - Credentials → use the stack's env/config convention (e.g., `config('key')` for Laravel, `process.env.VAR_NAME` for JS/TS, or the framework's idiomatic accessor)
   - Local paths → `<project-root>/...` or relative paths
   - Usernames → `<username>` placeholder
   - API endpoints → `https://api.example.com/...`
2. **Check `.gitignore`** — ensure the file is ignored if it should never be committed. Add it if missing
3. **Check `.env.example`** — add placeholder entry if a new env var is needed
4. **Never commit and then remove** — once a secret is in git history, it's compromised
5. **Only escalate to the user** if the private data is deeply embedded in logic and replacing it would change behavior

## Documentation and Skills

When committing SKILL.md, CLAUDE.md, or documentation:

- Replace real project names with generic terms if the repo is public
- Replace local paths with `<project-root>/...` or relative paths
- Replace usernames with `<username>` placeholders
- Replace API endpoints with `https://api.example.com/...`
