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
