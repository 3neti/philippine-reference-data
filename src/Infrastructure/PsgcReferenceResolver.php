<?php

namespace LBHurtado\PhilippineReferenceData\Infrastructure;

use LBHurtado\PhilippineReferenceData\Contracts\ReferenceDatasetRepository;
use LBHurtado\PhilippineReferenceData\Contracts\DetailedReferenceResolver;
use LBHurtado\PhilippineReferenceData\Data\ReferenceDatasetDto;
use LBHurtado\PhilippineReferenceData\Data\ReferenceRecordDto;
use LBHurtado\PhilippineReferenceData\Data\ReferenceResolutionDto;

final class PsgcReferenceResolver implements DetailedReferenceResolver
{
    /** @var array<string, string> */
    private array $versions;

    /** @var array<string, ReferenceDatasetDto> */
    private array $releases = [];

    /** @param array<string, string> $versions */
    public function __construct(private ReferenceDatasetRepository $repository, array $versions = ['philippine-geography' => '2026.3'])
    {
        $this->versions = $versions;
    }

    public function resolve(string $dataset, string $value, ?string $context = null): ?ReferenceRecordDto
    {
        $result = $this->resolveDetailed($dataset, $value, $context);
        return $result->isResolved() ? $result->record : null;
    }

    public function resolveDetailed(string $dataset, string $value, ?string $context = null): ReferenceResolutionDto
    {
        $records = $this->records($dataset, $context);
        $query = trim($value);
        $normalized = $this->normalize($query);

        foreach ($records as $record) {
            if ($record->key === $query) {
                return new ReferenceResolutionDto($dataset, $query, 'exact', $record, [$record]);
            }
        }

        $labelMatches = array_values(array_filter(
            $records,
            fn (ReferenceRecordDto $record): bool => $this->normalize($record->label) === $normalized,
        ));
        if (count($labelMatches) === 1) {
            return new ReferenceResolutionDto($dataset, $query, 'exact', $labelMatches[0], $labelMatches);
        }
        if ($labelMatches !== []) {
            return new ReferenceResolutionDto($dataset, $query, 'ambiguous', null, $labelMatches);
        }

        $aliases = [];
        foreach ($records as $record) {
            foreach ($this->aliases($record) as $alias) {
                if ($this->normalize($alias) === $normalized) {
                    $aliases[$record->type . ':' . $record->key] = $record;
                }
            }
        }

        $candidates = array_values($aliases);
        if (count($candidates) === 1) {
            return new ReferenceResolutionDto($dataset, $query, 'alias', $candidates[0], $candidates);
        }
        if ($candidates !== []) {
            return new ReferenceResolutionDto($dataset, $query, 'ambiguous', null, $candidates);
        }

        return new ReferenceResolutionDto($dataset, $query, 'unresolved');
    }

    /** @return list<ReferenceRecordDto> */
    private function records(string $dataset, ?string $context): array
    {
        $version = $this->versions[$dataset] ?? null;
        if ($version === null) {
            throw new \InvalidArgumentException("No dataset version configured for {$dataset}.");
        }

        $release = $this->releases[$dataset] ??= $this->repository->find($dataset, $version);
        if (! $release instanceof ReferenceDatasetDto) {
            throw new \RuntimeException("Reference dataset release not found: {$dataset} {$version}");
        }

        if ($context === null || $context === '') {
            return $release->records;
        }

        return array_values(array_filter(
            $release->records,
            static fn (ReferenceRecordDto $record): bool => $record->type === $context,
        ));
    }

    /** @return list<string> */
    private function aliases(ReferenceRecordDto $record): array
    {
        $value = $record->attributes['old_names'] ?? null;
        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn (string $alias): string => trim($alias),
            preg_split('/[;,|]+/', $value) ?: [],
        )));
    }

    private function normalize(string $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim($value)) ?? trim($value);
        return mb_strtolower($value, 'UTF-8');
    }
}
