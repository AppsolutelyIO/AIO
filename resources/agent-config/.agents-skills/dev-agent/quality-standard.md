# Quality Standard — Obsessive Attention to Detail

This is the non-negotiable baseline for all work performed by the dev agent.
Every output must meet the standard of a meticulous, senior-level craftsman.

## Code Precision

- **Naming**: every variable, method, class, and file must have a precise, self-documenting name. `$data` is never acceptable when `$orderItems` is what it holds
- **Consistency**: if the codebase uses `camelCase` for methods, one `snake_case` method is a bug. Match the exact style of sibling files — indentation, blank lines, brace placement, trailing commas
- **Imports**: no unused imports. No missing imports. Alphabetical order if that's the project convention
- **Whitespace**: no trailing spaces, no double blank lines, no inconsistent indentation. These are not cosmetic — they pollute diffs
- **Types**: every parameter typed, every return typed, every property typed. `mixed` is a last resort, not a default

## Logic Precision

- **Off-by-one errors**: verify loop boundaries, array indices, pagination offsets, and date range inclusivity
- **Null safety**: every nullable value must be handled. Never assume a relationship, config key, or array element exists without checking
- **Order of operations**: when multiple things happen in sequence, verify the order is correct. Does the event fire before or after the save? Does the cache clear before or after the update?
- **String details**: encoding, locale, timezone, trailing slashes, case sensitivity. These are where bugs hide
- **Edge values**: zero, empty string, empty array, null, boolean false — these are all different and must be handled differently

## Output Precision

- **Commit messages**: precise verb, precise scope, precise description. "Update stuff" is never acceptable
- **Code comments**: only when the logic is non-obvious. When present, they must be accurate — a wrong comment is worse than no comment
- **Test assertions**: assert the exact expected value, not just "not null" or "is array". A test that passes on wrong data is worse than no test
- **Error messages**: specific enough for the developer to know exactly what went wrong and where to look

## Review Discipline

The complete file editing cycle is: **read → edit → read-back**. Never skip a step.

Before considering any piece of work "done":

1. **Read before editing** — always re-read the file immediately before making changes. Never edit based on memory or prior context — the user may have modified the file between turns. Stale context leads to broken patches
2. **Read-back verification** — after every file write or edit, immediately read the file back to confirm the change was applied correctly. Never assume a write succeeded; verify it
3. Re-read every changed line as if reviewing someone else's PR
4. Check that no debug code (`dd()`, `dump()`, `console.log()`, `var_dump()`) remains
5. Verify every file touched follows the project's formatting rules
6. Confirm that new code is consistent with the patterns in surrounding code
7. Ask: "Would this survive a nitpick review from the most meticulous reviewer on the team?"

If the answer is no, fix it before moving on.
