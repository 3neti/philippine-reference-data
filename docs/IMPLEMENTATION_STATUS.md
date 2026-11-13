# Implementation Status

## Completed

- Composer package scaffold.
- Immutable dataset metadata and reference-record DTOs.
- Canonical JSON and SHA-256 hashing.
- Resolver and dataset-repository contracts.
- Provenance and licensing policy.
- PSA PSGC `2026.3` normalized release with source workbook, manifests, hashes,
  and a small regression fixture.
- Reproducible `composer dataset:build:psgc` dataset-generation command.
- JSON release repository and DTO hydration.
- PSGC exact, alias, ambiguous, and unresolved resolution outcomes.
- Non-destructive POP locality validation adapter.

## Not Yet Included

- ZIP-code records.
- Telephone-prefix records.
- Alias-resolution implementation.
- Official source ingestion and licensing confirmation.

## Next Slice

Add historical PSGC release selection and explicit alias governance. The EMS
should consume both POP and reference-data packages through an application
composition layer. ZIP-code and telephone data remain separate future
releases.
