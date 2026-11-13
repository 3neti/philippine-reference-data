<?php

namespace LBHurtado\PhilippineReferenceData\Contracts;

use LBHurtado\PhilippineReferenceData\Data\ReferenceResolutionDto;

interface DetailedReferenceResolver extends ReferenceResolver
{
    public function resolveDetailed(string $dataset, string $value, ?string $context = null): ReferenceResolutionDto;
}
