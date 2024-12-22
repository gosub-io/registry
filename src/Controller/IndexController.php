<?php

namespace App\Controller;

use App\Service\CrateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
    #[Route('/details/{crate}', name: 'details')]
    public function details(Request $request, string $crate, CrateService $crateService): Response {
        $versions = $crateService->getIndicesByCrate($crate);

        return $this->render('index/details.html.twig', [
            'selected_version' => $request->query->get('v', $versions[count($versions)-1]['vers'] ?? ''),
            'versions' => $versions,
        ]);
    }

    #[Route('/crates/{crate}', name: 'download')]
    public function downloads(Request $request, string $crate, CrateService $crateService): Response {
        $file = $this->file($crateService->getCratePath($crate));
        return new Response($file);
    }

    #[Route('/{crate}', name: 'index', requirements: ['crate' => '.*'], priority: -100)]
    public function index(Request $request, string $crate): Response {
        $crate = str_replace('-', '_', $crate);

        $basepath = realpath(
            $this->getParameter('kernel.project_dir') . '/' .
            $this->getParameter('path_index') . '/'
        );

        $realpath = realpath($basepath . '/' . $crate);
        if ($realpath === false || strpos($realpath, $basepath) !== 0) {
            throw new NotFoundHttpException("invalid crate url");
        }

        if (file_exists($realpath)) {
            $data = file_get_contents($realpath);
            return new Response($data, 200, ['Content-Type' => 'text/plain']);
        }

        throw new NotFoundHttpException("invalid crate url");
    }
}
