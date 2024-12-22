<?php

namespace App\Service;

class CrateService {
    protected string $crateDir;
    protected string $indexDir;

    function __construct(string $crateDir, string $indexDir) {
        $this->crateDir = $crateDir;
        $this->indexDir = $indexDir;
    }

    function getAllIndices(): array {
        return $this->scanCrates($this->indexDir, '');
    }

    function getIndicesByQuery(string $q): array {
        return $this->scanCrates($this->indexDir, $q);
    }

    protected function scanCrates(string $path, string $q): array {
        $crates = [];

        foreach (scandir($path) as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            if (is_dir($path . '/' . $file)) {
                $crates = array_merge($crates, $this->scanCrates($path . '/' . $file, $q));
                continue;
            }

            $index_lines = file($path . '/' . $file);
            if (count($index_lines) == 0) {
                continue;
            }
            $last_line = $index_lines[count($index_lines) - 1];
            $last_version = json_decode($last_line, true);
            if (!$last_version) {
                continue;
            }

            if ($q && strpos($last_version['name'], $q) === false) {
                continue;
            }

            $crates[] = [
                'name' => $last_version['name'],
                'max_version' => $last_version['vers'],
                'description' => $last_version['description'] ?? '',
            ];
        }

        return $crates;
    }

}