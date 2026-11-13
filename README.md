# `3neti/philippine-reference-data`

Framework-neutral, versioned reference data for the Philippine election EMS.

This package is intentionally separate from
[`3neti/project-of-precincts`](../project-of-precincts). The Project of
Precincts workbook remains the election-specific authority for precinct
topology. This package supplies reusable reference knowledge that the EMS may
use to validate, resolve, or enrich that source.

## Scope

The initial package boundary can contain:

- Philippine Standard Geographic Code (PSGC) hierarchy;
- locality names and approved aliases;
- ZIP or postal references;
- telephone prefixes; and
- historical or effective-date metadata.

No dataset is considered official merely because it is bundled here. Every
dataset must identify its issuing authority, publication/version, source URL or
delivery record, source hash, retrieval time, and license.

## Authority Rule

```text
Reference data may validate or enrich POP data.
Reference data must never silently replace POP data.
```

An ambiguous or failed match must become a reviewable issue. The EMS must
retain both the original source value and any resolved reference identity.

## Dataset Status

Supported provenance statuses are:

```text
official
official_derived
curated
unverified
```

`official` means the dataset was published by an identifiable authority and
its source provenance and license have been recorded. `official_derived` means
our normalized representation is derived from an official source but is not
itself the issuing authority's file.

## Domain Model

`ReferenceDatasetDto` identifies one immutable dataset release. It contains
`ReferenceRecordDto` values with a stable record key, dataset type, labels,
attributes, effective dates, and source provenance.

```php
$dataset = new ReferenceDatasetDto(
    metadata: new DatasetMetadataDto(
        dataset: 'philippine-geography',
        version: '2025.1',
        authority: 'PSA',
        status: 'official',
        sourceReference: 'https://example.invalid/source',
        sourceSha256: $sha256,
        retrievedAt: $retrievedAt,
        license: 'recorded-in-source-manifest',
    ),
    records: [
        new ReferenceRecordDto('city-municipality', 'PH-133900', 'Manila', ['region' => 'NCR']),
    ],
);
```

All public DTOs expose deterministic arrays, canonical JSON, and SHA-256
hashes. Historical datasets are never overwritten; a new publication becomes a
new dataset version.

## Resolver Boundary

The package defines `ReferenceResolver` as an optional integration boundary.
The POP package does not depend on this package. The EMS may provide a resolver
when it needs locality validation or enrichment.

```php
interface ReferenceResolver
{
    public function resolve(string $dataset, string $value, ?string $context = null): ?ReferenceRecordDto;
}
```

## Data Policy

The first published dataset is the PSA PSGC release `2026.3`. It is marked
`official_derived` because the package normalizes the PSA workbook into a
stable JSON representation. The original workbook, source manifest, hashes,
and a small regression fixture are retained in the repository. ZIP-code,
telephone-prefix, and alias datasets remain separate releases.

## Development

```bash
composer install
vendor/bin/pest
composer dataset:build:psgc
```

The dataset build command regenerates the committed PSA PSGC release from the
retained source workbook and must produce the same release hashes.

The package is PHP 8.4+, framework-neutral, and has no runtime dependencies.
