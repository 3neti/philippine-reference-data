# Dataset Policy

Each dataset release must retain:

- issuing authority;
- dataset name and version;
- source URL or delivery reference;
- source SHA-256;
- retrieval and effective dates;
- licensing terms;
- parser/normalizer version; and
- validation status.

For derived package formats, `status` must be `official_derived` unless the
issuing authority explicitly publishes the package format itself. The source
file must be retained or referenced by an immutable source manifest.

The package must preserve source values and normalized values separately. A
resolver may propose an identity for a POP value, but the EMS must retain the
original COMELEC value and record ambiguous matches as issues.

No dataset is bundled until provenance and redistribution rights are confirmed.
