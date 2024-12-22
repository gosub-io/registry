<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

class IndexController extends AbstractController
{
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
