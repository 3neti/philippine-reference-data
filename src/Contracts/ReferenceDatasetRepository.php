<?php

namespace LBHurtado\PhilippineReferenceData\Contracts;

use LBHurtado\PhilippineReferenceData\Data\ReferenceDatasetDto;

interface ReferenceDatasetRepository
{
    public function persist(ReferenceDatasetDto $dataset): void;

    public function find(string $dataset, string $version): ?ReferenceDatasetDto;
}
