<?php

namespace LBHurtado\PhilippineReferenceData\Data;

final readonly class ReferenceResolutionDto
{
    /** @param list<ReferenceRecordDto> $candidates */
    public function __construct(
        public string $dataset,
        public string $query,
        public string $status,
        public ?ReferenceRecordDto $record = null,
        public array $candidates = [],
    ) {
        if (! in_array($status, ['exact', 'alias', 'ambiguous', 'unresolved'], true)) {
            throw new \InvalidArgumentException('Reference resolution status is invalid.');
        }
    }

    public function isResolved(): bool
    {
        return $this->record !== null && in_array($this->status, ['exact', 'alias'], true);
    }

    public function toArray(): array
    {
        return [
            'dataset' => $this->dataset,
            'query' => $this->query,
            'status' => $this->status,
            'record' => $this->record?->toArray(),
            'candidates' => array_map(static fn (ReferenceRecordDto $record): array => $record->toArray(), $this->candidates),
        ];
    }
}
