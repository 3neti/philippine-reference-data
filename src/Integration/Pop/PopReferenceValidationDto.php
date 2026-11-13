<?php

namespace LBHurtado\PhilippineReferenceData\Integration\Pop;

use LBHurtado\PhilippineReferenceData\Data\ReferenceResolutionDto;

final readonly class PopReferenceValidationDto
{
    public function __construct(
        public string $sourceValue,
        public ?string $sourceType,
        public ReferenceResolutionDto $resolution,
    ) {}

    public function toArray(): array
    {
        return [
            'source_value' => $this->sourceValue,
            'source_type' => $this->sourceType,
            'resolution' => $this->resolution->toArray(),
        ];
    }
}
