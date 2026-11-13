<?php

namespace LBHurtado\PhilippineReferenceData\Infrastructure;

use LBHurtado\PhilippineReferenceData\Contracts\ReferenceDatasetRepository;
use LBHurtado\PhilippineReferenceData\Data\ReferenceDatasetDto;
use LBHurtado\PhilippineReferenceData\Support\CanonicalJson;

final class JsonReferenceDatasetRepository implements ReferenceDatasetRepository
{
    public function __construct(private string $rootDirectory) {}

    public function persist(ReferenceDatasetDto $dataset): void
    {
        $directory = $this->directory($dataset->metadata->dataset, $dataset->metadata->version);
        $path = $directory . '/dataset.json';

        if (is_file($path)) {
            throw new \RuntimeException("Reference dataset already exists: {$path}");
        }

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            throw new \RuntimeException("Unable to create dataset directory: {$directory}");
        }

        $temporary = tempnam($directory, 'dataset-');
        if ($temporary === false) {
            throw new \RuntimeException('Unable to create temporary dataset file.');
        }

        try {
            file_put_contents($temporary, CanonicalJson::encode($dataset->toArray()) . "\n", LOCK_EX);
            if (! rename($temporary, $path)) {
                throw new \RuntimeException("Unable to commit dataset file: {$path}");
            }
        } finally {
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
    }

    public function find(string $dataset, string $version): ?ReferenceDatasetDto
    {
        $path = $this->directory($dataset, $version) . '/dataset.json';
        if (! is_file($path)) {
            return null;
        }

        $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        return ReferenceDatasetDto::fromArray($decoded);
    }

    private function directory(string $dataset, string $version): string
    {
        return rtrim($this->rootDirectory, '/') . '/' . rawurlencode($dataset) . '/' . rawurlencode($version);
    }
}
