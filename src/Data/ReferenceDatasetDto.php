<?php

namespace LBHurtado\PhilippineReferenceData\Data;

use LBHurtado\PhilippineReferenceData\Support\CanonicalJson;

final readonly class ReferenceDatasetDto
{
    /** @param list<ReferenceRecordDto> $records */
    public function __construct(public DatasetMetadataDto $metadata, public array $records)
    {
        $keys = array_map(static fn (ReferenceRecordDto $record): string => $record->type . ':' . $record->key, $records);
        if (count($keys) !== count(array_unique($keys))) {
            throw new \InvalidArgumentException('Reference record keys must be unique within a dataset.');
        }
    }

    public function find(string $type, string $key): ?ReferenceRecordDto
    {
        foreach ($this->records as $record) {
            if ($record->type === $type && $record->key === $key) {
                return $record;
            }
        }
        return null;
    }

    public function toArray(): array
    {
        return ['package_type' => 'philippine-reference-dataset', 'schema_version' => 'reference-dataset.v1', 'metadata' => $this->metadata->toArray(), 'records' => array_map(static fn (ReferenceRecordDto $record): array => $record->toArray(), $this->records)];
    }

    public function hash(): string
    {
        return hash('sha256', CanonicalJson::encode($this->toArray()));
    }
}
