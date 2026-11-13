<?php

namespace LBHurtado\PhilippineReferenceData\Data;

use LBHurtado\PhilippineReferenceData\Support\CanonicalJson;

final readonly class ReferenceRecordDto
{
    /** @param array<string, scalar|null> $attributes */
    public function __construct(
        public string $type,
        public string $key,
        public string $label,
        public array $attributes = [],
        public ?string $effectiveFrom = null,
        public ?string $effectiveTo = null,
    ) {
        if (trim($this->type) === '' || trim($this->key) === '' || trim($this->label) === '') {
            throw new \InvalidArgumentException('Reference record identity must not be empty.');
        }
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            type: (string) $data['type'],
            key: (string) $data['key'],
            label: (string) $data['label'],
            attributes: (array) ($data['attributes'] ?? []),
            effectiveFrom: isset($data['effective_from']) ? (string) $data['effective_from'] : null,
            effectiveTo: isset($data['effective_to']) ? (string) $data['effective_to'] : null,
        );
    }

    public function toArray(): array
    {
        return ['type' => $this->type, 'key' => $this->key, 'label' => $this->label, 'attributes' => $this->attributes, 'effective_from' => $this->effectiveFrom, 'effective_to' => $this->effectiveTo];
    }

    public function hash(): string
    {
        return hash('sha256', CanonicalJson::encode($this->toArray()));
    }
}
