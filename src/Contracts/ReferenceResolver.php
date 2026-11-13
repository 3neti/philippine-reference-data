<?php

namespace LBHurtado\PhilippineReferenceData\Contracts;

use LBHurtado\PhilippineReferenceData\Data\ReferenceRecordDto;

interface ReferenceResolver
{
    public function resolve(string $dataset, string $value, ?string $context = null): ?ReferenceRecordDto;
}
