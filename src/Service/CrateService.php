<?php

namespace App\Service;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CrateService {
    protected string $crateDir;
    protected string $indexDir;

    function __construct(string $crateDir, string $indexDir) {
        $this->crateDir = $crateDir;
        $this->indexDir = $indexDir;
    }

    function getCratePath(string $crateName): string {
        $basePath = realpath($this->crateDir);
        $path = $basePath . '/' . $crateName;

        $realpath = realpath($path);
        if ($realpath === false || strpos($realpath, $basePath) !== 0) {
            throw new NotFoundHttpException("invalid path");
        }

        return $realpath;
    }

    function getAllIndices(): array {
        return $this->scanCrates($this->indexDir, '');
    }

    function getIndicesByQuery(string $q): array {
        return $this->scanCrates($this->indexDir, $q);
    }

    function getIndicesByCrate(string $name): array {
        $indexPath = $this->getIndexPath($name);
        if (!file_exists($indexPath)) {
            return [];
        }

        $index_lines = file($indexPath);
        $crates = [];
        foreach ($index_lines as $line) {
            $data = json_decode($line, true);
            if ($data) {
                $crates[] = $data;
            }
        }

        return $crates;
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


    /**
     * Convert a crate name to an index path
     */
    protected function getIndexPath($crateName): string
    {
        $path = $this->indexDir . '/';

        if (strlen($crateName) == 1) {
            $path .= '1/' . $crateName;
        } else if (strlen($crateName) == 2) {
            $path .= '2/' . $crateName;
        } else if (strlen($crateName) == 3) {
            $path .= '3/' . $crateName[0] . '/' . $crateName;
        } else {
            $path .= substr($crateName, 0, 2) . '/'. substr($crateName, 2, 2) . '/' . $crateName;
        }

        return $path;
    }


}