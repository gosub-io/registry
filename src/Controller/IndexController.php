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

        $path =
            $this->getParameter('kernel.project_dir') . '/' .
            $this->getParameter('path_index') . '/' .
            $crate
        ;

        $realpath = realpath($path);
        if ($realpath === false || strpos($realpath, $this->getParameter('kernel.project_dir')) !== 0) {
            throw new NotFoundHttpException("invalid crate url");
        }

        if (file_exists($path)) {
            $data = file_get_contents($path);
            return new Response($data, 200, ['Content-Type' => 'text/plain']);
        }

        throw new NotFoundHttpException("invalid crate url");
    }
}
