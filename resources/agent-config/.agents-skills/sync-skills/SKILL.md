---
name: sync-skills
description: Synchronize and consolidate agent skills across .agent/, .claude/, .cursor/ directories into the centralized .agents/skills/ directory. Use when adding new skills, resolving duplicates, or ensuring all consumer directories have correct symlinks.
---

# Sync Skills

Consolidate and synchronize skills across all agent tool directories, using `.agents/skills/` as the single source of truth.

## When to Use This Skill

Use this skill when the user:

- Installs a new skill and needs it synced to all directories
- Notices skills are out of sync between `.agents/`, `.claude/`, `.cursor/`, `.agent/`
- Wants to merge skills from one directory into the central `.agents/skills/`
- Asks to "sync skills", "consolidate skills", "unify skills", or "update skill symlinks"
- Adds a skill manually to one directory and wants it everywhere

## Architecture

```
.agents/skills/          ← Single source of truth (real files live here)

.agent/skills/           ← Universal standard (Codex, Google Gemini CLI)
  └── symlinks → ../../.agents/skills/*

.claude/skills/          ← Claude Code
  └── symlinks → ../../.agents/skills/*

.cursor/skills/          ← Cursor IDE
  └── symlinks → ../../.agents/skills/*
```

## Workflow

### 1. Audit Current State

First, scan all four directories to understand what exists where:

```bash
echo "=== .agents/skills (source) ===" && ls .agents/skills/
echo "=== .agent/skills ===" && ls -la .agent/skills/ 2>/dev/null
echo "=== .claude/skills ===" && ls -la .claude/skills/ 2>/dev/null
echo "=== .cursor/skills ===" && ls -la .cursor/skills/ 2>/dev/null
```

### 2. Identify Discrepancies

Check for:

- **Real directories in consumer dirs** — These should be symlinks, not copies. A real directory means a skill was copied/installed directly instead of symlinked.
- **Missing symlinks** — Skills in `.agents/skills/` that don't have corresponding symlinks in consumer directories.
- **Orphaned symlinks** — Symlinks in consumer directories that point to non-existent targets.
- **Duplicate skills with differences** — Same skill name exists in multiple places with different content.

```bash
# Find real directories (not symlinks) in consumer dirs
for dir in .agent/skills .claude/skills .cursor/skills; do
  echo "=== Real dirs in $dir ==="
  for item in $dir/*/; do
    name=$(basename "$item")
    if [ -d "$dir/$name" ] && [ ! -L "$dir/$name" ]; then
      echo "  $name (real directory, should be symlink)"
    fi
  done
done
```

### 3. Handle Duplicates (Commit-First Strategy)

**Critical**: When a skill exists as a real directory in a consumer dir AND in `.agents/skills/`, always commit the current `.agents/skills/` state first before overwriting. This preserves version history.

```bash
# Step 1: Commit current state
git add .agents/skills/<skill-name>
git commit -m "chore(skills): snapshot <skill-name> before merge"

# Step 2: Compare
diff -rq .agents/skills/<skill-name> .claude/skills/<skill-name>

# Step 3: If different, copy the newer version into .agents/skills/
cp -R .claude/skills/<skill-name>/* .agents/skills/<skill-name>/

# Step 4: Commit the update
git add .agents/skills/<skill-name>
git commit -m "chore(skills): update <skill-name> from .claude"
```

If files are identical, skip the copy — just replace the real directory with a symlink.

### 4. Replace Real Directories with Symlinks

After merging content into `.agents/skills/`, replace real directories in consumer dirs:

```bash
# For each consumer directory
for dir in .agent/skills .claude/skills .cursor/skills; do
  for item in $dir/*/; do
    name=$(basename "$item")
    # Skip if already a symlink
    if [ -L "$dir/$name" ]; then continue; fi
    # Skip if not in .agents/skills
    if [ ! -d ".agents/skills/$name" ]; then continue; fi
    # Replace with symlink
    rm -rf "$dir/$name"
    ln -s "../../.agents/skills/$name" "$dir/$name"
    echo "Symlinked: $dir/$name → .agents/skills/$name"
  done
done
```

### 5. Add Missing Symlinks

Ensure every skill in `.agents/skills/` has symlinks in all consumer directories:

```bash
for skill in .agents/skills/*/; do
  name=$(basename "$skill")
  for dir in .agent/skills .claude/skills .cursor/skills; do
    mkdir -p "$dir"
    if [ ! -e "$dir/$name" ]; then
      ln -s "../../.agents/skills/$name" "$dir/$name"
      echo "Added: $dir/$name"
    fi
  done
done
```

### 6. Clean Up Orphaned Symlinks

Remove symlinks that point to non-existent targets:

```bash
for dir in .agent/skills .claude/skills .cursor/skills; do
  for link in $dir/*; do
    if [ -L "$link" ] && [ ! -e "$link" ]; then
      echo "Removing broken symlink: $link"
      rm "$link"
    fi
  done
done
```

### 7. Commit

```bash
git add .agents/skills/ .agent/skills/ .claude/skills/ .cursor/skills/
git commit -m "chore(skills): sync all skill directories"
```

## Quick Sync (One-Shot Command)

For a fast full sync when you know `.agents/skills/` is already correct:

```bash
# Ensure consumer dirs exist
mkdir -p .agent/skills .claude/skills .cursor/skills

# Sync all three consumer directories
for skill in .agents/skills/*/; do
  name=$(basename "$skill")
  for dir in .agent/skills .claude/skills .cursor/skills; do
    if [ ! -L "$dir/$name" ]; then
      rm -rf "$dir/$name" 2>/dev/null
      ln -s "../../.agents/skills/$name" "$dir/$name"
    fi
  done
done

echo "Synced $(ls .agents/skills/ | wc -l | tr -d ' ') skills to all directories"
```

## Adding a New Skill

When installing a new skill via `npx skills add`:

1. The CLI installs to `.agents/skills/` and auto-creates symlinks in `.claude/skills/` and `.agent/skills/`
2. You still need to manually add the `.cursor/skills/` symlink:

```bash
ln -s "../../.agents/skills/<skill-name>" ".cursor/skills/<skill-name>"
```

3. Commit:

```bash
git add .agents/skills/<skill-name> .agent/skills/<skill-name> .claude/skills/<skill-name> .cursor/skills/<skill-name>
git commit -m "feat(skills): add <skill-name>"
```

## Rules

- **Never edit skills in consumer directories** — Always edit in `.agents/skills/`, changes propagate via symlinks.
- **Always commit before overwriting** — When merging duplicates, commit the current state first for version history.
- **One skill per commit** — When merging multiple duplicates, commit each skill separately for clean rollback.
- **Check for differences before replacing** — Don't blindly overwrite; use `diff -rq` to compare first.
- **Empty placeholder directories are OK** — Consumer dirs may have empty dirs for planned-but-not-yet-implemented skills.
