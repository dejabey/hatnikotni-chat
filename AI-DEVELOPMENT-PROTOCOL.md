# AI Development Protocol — Hatnikotni Chat

This is the development workflow contract for AI-assisted work.

## Core rule

A material change is complete only after:

SOURCE → TEST/CONTRACT → BUILD/CI → DOCUMENTATION → CURRENT HANDOFF → RUNTIME STATUS

The repository must contain enough information for a new chat or developer to continue without relying on conversation history.

## Current source of truth

PROJECT-HANDOFF.md is the single authoritative current-state document.

Historical audit documents are traceability records only.

## Required material-change updates

Evaluate and update as applicable:

- PROJECT-HANDOFF.md
- CHANGELOG.md
- README.md
- architecture documentation
- tests/contracts

Do not silently omit documentation. If no update is required, record that decision.

## Testing discipline

Distinguish:

- source/syntax validation;
- automated contract/CI validation;
- staging/runtime acceptance;
- production acceptance.

CI success never proves runtime acceptance.

## Runtime vocabulary

Use only:

- CONFIRMED
- PENDING
- FAILED
- NOT APPLICABLE

Never mark runtime behaviour CONFIRMED from source inspection alone.

## Release path

Development → GitHub → review → release candidate → staging → functional/mobile/desktop/cache/accessibility/performance/security testing → production.

Production is not an automatic deployment target.

## Failure discipline

When a failure occurs:

1. capture the exact failure;
2. trace the source path;
3. fix the root cause;
4. add or strengthen a guard/test;
5. rebuild;
6. retest;
7. update the handoff and changelog.

Do not hide an unresolved failure with a workaround.

## Chat continuity

Every material development response must state:

1. current commit/HEAD;
2. build/CI state;
3. documentation state;
4. runtime acceptance state;
5. exact next action.

The repository, not the chat transcript, is the continuity mechanism.
