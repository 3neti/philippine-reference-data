<?php

use LBHurtado\PhilippineReferenceData\Data\DatasetMetadataDto;
use LBHurtado\PhilippineReferenceData\Data\ReferenceDatasetDto;
use LBHurtado\PhilippineReferenceData\Data\ReferenceRecordDto;

it('creates a provenance-backed deterministic dataset', function (): void {
    $dataset = new ReferenceDatasetDto(
        new DatasetMetadataDto('philippine-geography', '2025.1', 'PSA', 'official', 'source', str_repeat('a', 64), '2026-11-13T00:00:00+08:00', 'recorded'),
        [new ReferenceRecordDto('city-municipality', 'PH-133900', 'Manila', ['region' => 'NCR'])],
    );

    expect($dataset->find('city-municipality', 'PH-133900')->label)->toBe('Manila')
        ->and($dataset->hash())->toHaveLength(64)
        ->and($dataset->toArray()['schema_version'])->toBe('reference-dataset.v1');
});

it('loads the published PSGC release and preserves its provenance', function (): void {
    $path = dirname(__DIR__, 2) . '/resources/datasets/philippine-geography/2026.3/dataset.json';
    $dataset = ReferenceDatasetDto::fromArray(json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR));

    expect($dataset->metadata->dataset)->toBe('philippine-geography')
        ->and($dataset->metadata->version)->toBe('2026.3')
        ->and($dataset->metadata->status)->toBe('official_derived')
        ->and($dataset->metadata->sourceSha256)->toBe('8aad47f84e4f6317dc4222b9fe35d36b5d71f2b74ad8f0c7622289c131dc9a59')
        ->and($dataset->records)->toHaveCount(43768)
        ->and($dataset->find('city', '1380600000')->label)->toBe('City of Manila');
});

it('loads the small PSGC regression fixture', function (): void {
    $path = dirname(__DIR__) . '/Fixtures/psgc-small.json';
    $dataset = ReferenceDatasetDto::fromArray(json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR));

    expect($dataset->records)->toHaveCount(3)
        ->and($dataset->find('region', '1300000000')->label)->toBe('National Capital Region (NCR)')
        ->and($dataset->find('barangay', '1380600001')->attributes['source_geographic_level'])->toBe('Bgy');
});
