# WordPress Coding Standards

## Current status

The full-repository PHPCS audit completed with **zero errors and zero warnings** in GitHub Actions run #833 on 5 November 2026.

The previous 91-error / 67-warning baseline from Build #753 has been resolved. No legacy PHPCS baseline is being carried forward.

## Continuous enforcement

- The workflow runs PHPCS against all production PHP files on PHP 8.1.
- The full-repository PHPCS step is a required CI gate; any error or warning fails the job.
- The incremental changed-line check also runs across the PHP 8.1–8.5 matrix.
- No broad PHPCS exclusions or blanket suppressions are used.
- Any justified exception for an operation that cannot use the WordPress Filesystem API must include a local explanation.

This document records the current clean baseline. Update it only after a full-repository PHPCS run confirms a new clean state.
