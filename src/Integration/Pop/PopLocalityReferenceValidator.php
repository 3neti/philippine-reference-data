<?php

namespace LBHurtado\PhilippineReferenceData\Integration\Pop;

use LBHurtado\PhilippineReferenceData\Contracts\DetailedReferenceResolver;
use LBHurtado\PhilippineReferenceData\Contracts\ReferenceResolver;
use LBHurtado\PhilippineReferenceData\Data\ReferenceResolutionDto;

final readonly class PopLocalityReferenceValidator
{
    public function __construct(private ReferenceResolver $resolver, private string $dataset = 'philippine-geography') {}

    public function validate(string $sourceValue, ?string $sourceType = null): PopReferenceValidationDto
    {
        $resolution = $this->resolver instanceof DetailedReferenceResolver
            ? $this->resolver->resolveDetailed($this->dataset, $sourceValue, $sourceType)
            : $this->fallbackResolution($sourceValue, $sourceType);

        return new PopReferenceValidationDto($sourceValue, $sourceType, $resolution);
    }

    /** @param iterable<array{value: string, type?: string|null}> $entries @return list<PopReferenceValidationDto> */
    public function validateEntries(iterable $entries): array
    {
        $results = [];
        foreach ($entries as $entry) {
            $results[] = $this->validate($entry['value'], $entry['type'] ?? null);
        }
        return $results;
    }

    private function fallbackResolution(string $sourceValue, ?string $sourceType): ReferenceResolutionDto
    {
        $record = $this->resolver->resolve($this->dataset, $sourceValue, $sourceType);
        return new ReferenceResolutionDto(
            $this->dataset,
            $sourceValue,
            $record === null ? 'unresolved' : 'exact',
            $record,
            $record === null ? [] : [$record],
        );
    }
}
