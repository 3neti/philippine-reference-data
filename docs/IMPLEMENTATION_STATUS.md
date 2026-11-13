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

## Not Yet Included

- ZIP-code records.
- Telephone-prefix records.
- Alias-resolution implementation.
- Official source ingestion and licensing confirmation.

## Next Slice

Add a JSON repository/loader for versioned releases, then add explicit PSGC
alias resolution and a POP integration adapter. ZIP-code and telephone data
remain separate future releases.
