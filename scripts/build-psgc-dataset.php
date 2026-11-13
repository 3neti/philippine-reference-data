<?php

declare(strict_types=1);

if ($argc !== 3) {
    fwrite(STDERR, "Usage: php scripts/build-psgc-dataset.php SOURCE.xlsx OUTPUT_DIR\n");
    exit(2);
}

$source = $argv[1];
$output = rtrim($argv[2], '/');
$sourceHash = hash_file('sha256', $source);
$sourceReference = 'https://psa.gov.ph/system/files/scd/PSGC-3Q-2026-Publication-Datafile.xlsx';
$retrievedAt = '2026-11-14T06:43:41+08:00';
$normalizerVersion = 'psgc-normalizer/1.0.0';
$namespace = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

function canonicalJson(mixed $value): string
{
    return json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION);
}

function writeJson(string $path, mixed $value): string
{
    $json = canonicalJson($value) . "\n";
    file_put_contents($path, $json, LOCK_EX);
    return hash('sha256', $json);
}

function textValue(SimpleXMLElement $cell, array $sharedStrings): ?string
{
    $value = $cell->v;
    if ($value === null) {
        return null;
    }

    $text = (string) $value;
    $attributes = $cell->attributes();
    if ((string) ($attributes['t'] ?? '') === 's') {
        return $sharedStrings[(int) $text] ?? null;
    }

    return trim($text) === '' ? null : trim($text);
}

function normalizeNullable(?string $value): ?string
{
    $value = trim((string) $value);
    return $value === '' ? null : preg_replace('/\s+/u', ' ', $value);
}

$zip = new ZipArchive();
if ($zip->open($source) !== true) {
    throw new RuntimeException('Unable to open source workbook.');
}

$shared = simplexml_load_string($zip->getFromName('xl/sharedStrings.xml'));
$shared->registerXPathNamespace('a', $namespace);
$sharedStrings = [];
foreach ($shared->si as $item) {
    $parts = [];
    foreach ($item->children($namespace) as $child) {
        if ($child->getName() === 't') {
            $parts[] = (string) $child;
            continue;
        }
        foreach ($child->children($namespace)->t as $text) {
            $parts[] = (string) $text;
        }
    }
    $sharedStrings[] = implode('', $parts);
}

$reader = new XMLReader();
$reader->XML($zip->getFromName('xl/worksheets/sheet4.xml'));
$headers = [];
$records = [];
$sourceRowsRead = 0;
$excludedRows = [43771 => 'Publication note outside the PSGC table.'];
$levelMap = [
    'Reg' => 'region',
    'Prov' => 'province',
    'City' => 'city',
    'Mun' => 'municipality',
    'SubMun' => 'submunicipality',
    'Bgy' => 'barangay',
];

while ($reader->read()) {
    if ($reader->nodeType !== XMLReader::ELEMENT || $reader->localName !== 'row') {
        continue;
    }

    $rowNumber = (int) $reader->getAttribute('r');
    $row = simplexml_load_string($reader->readOuterXml());
    $cells = [];
    foreach ($row->children($namespace)->c as $cell) {
        $attributes = $cell->attributes();
        $ref = (string) $attributes['r'];
        $column = preg_replace('/\d+$/', '', $ref);
        $cells[$column] = textValue($cell, $sharedStrings);
    }

    if ($rowNumber === 1) {
        foreach ($cells as $column => $label) {
            if ($label !== null) {
                $headers[$label] = $column;
            }
        }
        continue;
    }

    if ($rowNumber === 43771 || ! isset($cells['B'])) {
        continue;
    }

    $sourceRowsRead++;
    $sourceLevel = normalizeNullable($cells['E'] ?? null);
    $label = normalizeNullable($cells['C'] ?? null);
    $key = normalizeNullable($cells['B'] ?? null);
    if ($key === null || $label === null) {
        throw new RuntimeException("Missing PSGC identity at source row {$rowNumber}.");
    }

    $type = $levelMap[$sourceLevel] ?? null;
    $note = null;
    if ($type === null && str_contains($label, 'City of Isabela')) {
        $type = 'city';
        $note = 'Source geographic level is blank; type derived from source name.';
    } elseif ($type === null && $label === 'Special Geographic Area') {
        $type = 'special-geographic-area';
        $note = 'Source geographic level is blank; retained as a named PSA geographic entry.';
    } elseif ($type === null) {
        throw new RuntimeException("Unsupported geographic level at source row {$rowNumber}.");
    }

    $population = normalizeNullable($cells['J'] ?? null);
    if ($population === '#N/A') {
        $population = null;
    }

    $attributes = [
        'correspondence_code' => normalizeNullable($cells['D'] ?? null),
        'source_geographic_level' => $sourceLevel,
        'old_names' => normalizeNullable($cells['F'] ?? null),
        'city_class' => normalizeNullable($cells['G'] ?? null),
        'income_classification' => normalizeNullable($cells['H'] ?? null),
        'urban_rural' => normalizeNullable($cells['I'] ?? null),
        'population_2024' => $population === null ? null : (int) $population,
        'status' => normalizeNullable($cells['L'] ?? null),
        'source_row' => $rowNumber,
        'normalization_note' => $note,
    ];

    $records[] = [
        'type' => $type,
        'key' => $key,
        'label' => $label,
        'attributes' => $attributes,
        'effective_from' => null,
        'effective_to' => null,
    ];
}
$reader->close();
$zip->close();

usort($records, static fn (array $left, array $right): int => [$left['key'], $left['type']] <=> [$right['key'], $right['type']]);

$metadata = [
    'dataset' => 'philippine-geography',
    'version' => '2026.3',
    'authority' => 'Philippine Statistics Authority (PSA)',
    'status' => 'official_derived',
    'sourceReference' => $sourceReference,
    'sourceSha256' => $sourceHash,
    'retrievedAt' => $retrievedAt,
    'license' => 'CC BY 4.0 (PSA/GOVPH site terms; verify attachment-specific terms before redistribution)',
    'effectiveFrom' => '2026-09-30',
    'effectiveTo' => null,
    'normalizerVersion' => $normalizerVersion,
];

$dataset = [
    'package_type' => 'philippine-reference-dataset',
    'schema_version' => 'reference-dataset.v1',
    'metadata' => $metadata,
    'records' => $records,
];

if (! is_dir($output) && ! mkdir($output, 0775, true) && ! is_dir($output)) {
    throw new RuntimeException("Unable to create output directory: {$output}");
}

$datasetHash = writeJson("{$output}/dataset.json", $dataset);
$recordsHash = writeJson("{$output}/records.json", $records);
$sourceManifestHash = writeJson("{$output}/source.json", [
    'authority' => $metadata['authority'],
    'publication' => 'PSGC as of 30 September 2026',
    'source_reference' => $sourceReference,
    'source_file' => 'PSGC-3Q-2026-Publication-Datafile.xlsx',
    'source_sha256' => $sourceHash,
    'retrieved_at' => $retrievedAt,
    'license' => $metadata['license'],
    'sheet' => 'PSGC',
    'source_rows_read' => $sourceRowsRead,
    'excluded_rows' => $excludedRows,
]);
writeJson("{$output}/manifest.json", [
    'dataset' => $metadata['dataset'],
    'version' => $metadata['version'],
    'status' => $metadata['status'],
    'dataset_sha256' => $datasetHash,
    'records_sha256' => $recordsHash,
    'source_manifest_sha256' => $sourceManifestHash,
    'source_sha256' => $sourceHash,
    'record_count' => count($records),
    'source_rows_read' => $sourceRowsRead,
    'normalizer_version' => $normalizerVersion,
]);

printf("Built %d records\nDataset SHA-256: %s\n", count($records), $datasetHash);
