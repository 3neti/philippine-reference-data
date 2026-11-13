# Philippine Geography `2026.3`

This release is a normalized, package-readable representation of the PSA
Philippine Standard Geographic Code publication as of 30 September 2026.

## Provenance

- Authority: Philippine Statistics Authority (PSA)
- Publication: PSGC as of 30 September 2026
- Source: `PSGC-3Q-2026-Publication-Datafile.xlsx`
- Source URL: <https://psa.gov.ph/system/files/scd/PSGC-3Q-2026-Publication-Datafile.xlsx>
- Source SHA-256: `8aad47f84e4f6317dc4222b9fe35d36b5d71f2b74ad8f0c7622289c131dc9a59`
- Retrieved: 14 November 2026, 06:43:41 Philippine Standard Time
- License recorded from PSA/GOVPH site: CC BY 4.0, subject to attachment-specific verification
- Package status: `official_derived`
- Normalizer: `psgc-normalizer/1.0.0`

## Contents

- `dataset.json`: metadata and all normalized `ReferenceRecordDto` records
- `records.json`: records-only view for tooling and inspection
- `source.json`: source and normalization manifest
- `manifest.json`: release hashes and counts

The source workbook is retained at
`resources/sources/psa/2026-09-30/PSGC-3Q-2026-Publication-Datafile.xlsx`.

The release contains 43,768 normalized records: regions, provinces, cities,
municipalities, submunicipalities, barangays, and the named Special Geographic
Area entry. The two source rows with blank geographic level are retained using
explicit normalization notes; no source data is silently discarded.
