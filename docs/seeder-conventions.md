# Seeder Conventions

## Default Pattern: `updateOrCreate()`

All seeders **must** use `updateOrCreate()` as the default write pattern.

```php
NotificationTemplate::updateOrCreate(
    ['slug' => $template['slug']],   // unique key(s) for matching
    $template,                        // all fields to create or update
);
```

### Why `updateOrCreate()`

- **Idempotent**: safe to run multiple times, always produces the same result.
- **Non-destructive**: preserves user-created records (unlike `truncate()`).
- **Keeps defaults in sync**: when seeder data changes in code, re-running the seeder updates existing records. `firstOrCreate()` cannot do this — it only creates, never updates.

### Do NOT use

| Pattern | Problem |
|---|---|
| `firstOrCreate()` | Existing records are never updated when seeder data changes in code. |
| `truncate()` + `insert()`/`create()` | Destroys all user-created records. See exception below. |
| Manual `exists()` + `create()` | Verbose reimplementation of `firstOrCreate()`, same problem. |

## Exception: Full-replacement Seeders

Seeders whose purpose is to **reset an entire table to a predefined profile** (e.g. admin menu profiles) may use `truncate()` + `create()`.

```php
// AdminMenuFullSeeder — replaces entire menu with the "full" profile
Menu::truncate();

Menu::create([...]);
Menu::create([...]);

(new Menu())->flushCache();
```

This is acceptable because the semantic intent is "discard everything and replace", not "ensure these records exist".

## Choosing a Unique Key

Every `updateOrCreate()` call needs a unique key (first argument). Use a stable, human-readable identifier:

- `slug` — for templates, senders, etc.
- `key` — for settings
- Composite keys (`['class' => ..., 'block_group_id' => ...]`) — when a single field is not unique enough

Never use auto-increment `id` as the unique key — it is not stable across environments.
