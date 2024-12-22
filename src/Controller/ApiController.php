<?php

namespace App\Controller;

use App\Service\CrateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ApiController extends AbstractController
{
    #[Route('/api/v1/crates/new', name: 'upload')]
    public function upload(Request $request, CrateService $crateService): JsonResponse
    {
        $data = $request->getContent();

        $json_len = unpack("L", $data);
        $json = substr($data, 4, $json_len[1]);
        $json = json_decode($json, true);

        $crate_len = unpack("L", substr($data, 4 + $json_len[1]));
        $crate_data = substr($data, 4 + $json_len[1] + 4, $crate_len[1]);
        $crate_filename = str_replace('-', '_', $json['name']);

        $crate_path =
            $this->getParameter('kernel.project_dir') . '/' .
            $this->getParameter('path_crates') . '/' .
            $crate_filename . '-'.$json['vers'].'.crate'
        ;
        file_put_contents($crate_path, $crate_data);

        $path = $crateService->getCratePath($crate_filename);
        if (!file_exists(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        $json_line = $this->convertToIndex($json, $crate_data);
        file_put_contents($path, $json_line, FILE_APPEND);

        return new JsonResponse([]);
    }

    #[Route('/api/v1/crates', name: 'crates')]
    public function crates(Request $request, CrateService $crateService): JsonResponse{
        $q = $request->query->get('q', '');
        $per_page = $request->query->get('per_page', 10);

        $crates = $crateService->getIndicesByQuery($q);

        return new JsonResponse([
            'crates' => array_slice($crates, 0, $per_page),
            'meta' => [
                'total' => count($crates),
            ],
        ]);
    }

    protected function convertToIndex(array $json, string $crate_data)
    {
        // The metadata that we need to store in the index is a little bit different
        // than we get from the upload. So we need to change some things around a bit.

        $json['cksum'] = hash('sha256', $crate_data);
        $json['yanked'] = false;

        foreach ($json['deps'] as &$dep) {
            if (isset($dep['explicit_name_in_toml'])) {
                $dep['package'] = $dep['name'];
                $dep['name'] = $dep['explicit_name_in_toml'];
                unset($dep['explicit_name_in_toml']);
            }
            $dep['req'] = $dep['version_req'];
            unset($dep['version_req']);
        }

        // Features in the root is a {}, not a []  !!
        if (! $json['features']) {
            $json['features'] = (object) $json['features'];
        }

        unset($json['rust_version']);

        return json_encode($json, JSON_UNESCAPED_SLASHES) . "\n";
    }
}
