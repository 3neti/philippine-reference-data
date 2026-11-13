<?php

namespace LBHurtado\PhilippineReferenceData\Data;

use LBHurtado\PhilippineReferenceData\Support\CanonicalJson;

final readonly class DatasetMetadataDto
{
    public function __construct(
        public string $dataset,
        public string $version,
        public string $authority,
        public string $status,
        public string $sourceReference,
        public string $sourceSha256,
        public string $retrievedAt,
        public string $license,
        public ?string $effectiveFrom = null,
        public ?string $effectiveTo = null,
    ) {
        if (! in_array($status, ['official', 'official_derived', 'curated', 'unverified'], true)) {
            throw new \InvalidArgumentException('Dataset status is invalid.');
        }
        if (! preg_match('/^[a-f0-9]{64}$/i', $sourceSha256)) {
            throw new \InvalidArgumentException('sourceSha256 must be a SHA-256 hash.');
        }
    }

    /** @return array<string, string|null> */
    public function toArray(): array
    {
        return get_object_vars($this);
    }

    public function canonicalJson(): string
    {
        return CanonicalJson::encode($this->toArray());
    }

    public function hash(): string
    {
        return hash('sha256', $this->canonicalJson());
    }
}
