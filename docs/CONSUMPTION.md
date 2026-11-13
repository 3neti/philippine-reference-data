# Dataset Consumption

## Loading a release

The JSON repository loads an immutable release directory without introducing a
framework dependency:

```php
use LBHurtado\PhilippineReferenceData\Infrastructure\JsonReferenceDatasetRepository;

$repository = new JsonReferenceDatasetRepository(__DIR__ . '/resources/datasets');
$dataset = $repository->find('philippine-geography', '2026.3');
```

The release is selected by dataset name and version. Applications must record
the selected release hash with the import or ceremony evidence that consumed
it.

## PSGC resolution

```php
use LBHurtado\PhilippineReferenceData\Infrastructure\PsgcReferenceResolver;

$resolver = new PsgcReferenceResolver($repository);
$result = $resolver->resolveDetailed('philippine-geography', 'City of Manila', 'city');
```

Resolution statuses are:

- `exact`: PSGC code or current label matched uniquely;
- `alias`: a recorded source alias matched uniquely;
- `ambiguous`: multiple records matched, so no record is selected; and
- `unresolved`: no record matched.

The nullable `ReferenceResolver::resolve()` method returns a record only for a
unique `exact` or `alias` result. It returns `null` for ambiguity and misses so
callers cannot silently select an arbitrary locality.

## POP validation

`PopLocalityReferenceValidator` accepts source values from the POP importer and
returns validation evidence. It does not mutate, replace, or rewrite the POP
value:

```php
use LBHurtado\PhilippineReferenceData\Integration\Pop\PopLocalityReferenceValidator;

$validator = new PopLocalityReferenceValidator($resolver);
$validation = $validator->validate('City of Manila', 'city');
```

The application should retain both `$validation->sourceValue` and the optional
reference record. Ambiguous or unresolved values become reviewable issues in
the EMS import result.

## EMS dependency direction

The EMS consumes both packages:

```text
COMELEC POP workbook -> 3neti/project-of-precincts -> authoritative POP snapshot
                                             \
                                              -> EMS validation using 3neti/philippine-reference-data
PSA PSGC release ----> 3neti/philippine-reference-data
```

The POP package does not depend on the reference-data package. PSGC data may
validate or enrich POP records, but it cannot silently replace COMELEC source
values or establish election topology that is absent from the POP workbook.
