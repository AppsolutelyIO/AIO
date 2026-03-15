# Context Continuity

In long conversations, decisions made early can be forgotten. Prevent this.

## Decision Log

At each major decision point, output a brief summary:

> **Decision**: [what was decided]
> **Reason**: [why this approach over alternatives]
> **Affects**: [which files/components]

This creates an in-conversation audit trail that survives context compression.

## State Checkpoints

Scale checkpoint detail to the change size (mirrors Adaptive Workflow tiers):

- **Small changes** — skip checkpoints entirely. The commit message is the checkpoint.
- **Medium changes** — a one-line status update after each commit is sufficient.
- **Large changes** — at natural milestones, summarize:
  1. **What has been done so far** — files modified, commits made, tests passing
  2. **What remains** — pending steps, known issues
  3. **Active constraints** — decisions that constrain future steps

## Recovery from Context Loss

If you notice prior context has been compressed or lost, **reconstruct autonomously**:

1. Re-read all modified files to reconstruct what was done
2. Check `git log --oneline -10` and `git diff` for recent changes
3. Check `git stash list` for any stashed work
4. Review memory files for saved decisions
5. Cross-reference the original user request with current file state
6. **Only ask the user if steps 1–5 leave genuine ambiguity** — e.g., you cannot determine which of two directions was chosen

Never silently proceed with stale assumptions. But also never ask the user for
information you can recover from git and the filesystem.

## Post-mortem

Scale retrospective depth to the change size:

- **Small changes** — skip post-mortem. Move on.
- **Medium changes** — quick mental check: did I solve the right problem? Anything left unfinished?
- **Large changes** — conduct a full retrospective:

1. **Did I solve the right problem?** — revisit the original request
2. **Is anything left unfinished?** — TODO comments, disabled tests, temporary workarounds
3. **Did I introduce technical debt?** — if so, is it documented and intentional?
4. **What did I learn?** — new patterns, gotchas, undocumented behavior worth remembering
5. **Are there follow-up tasks?** — related improvements discovered during implementation

### Memory Update

If the task revealed something reusable across sessions:

- A recurring project pattern → update memory files
- A package quirk or undocumented behavior → record it
- A user preference discovered through interaction → save it

Do not save session-specific or speculative information.
