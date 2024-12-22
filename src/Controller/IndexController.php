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

        $crate_path = $this->getParameter('kernel.project_dir') . '/public/index/' . $crate;
        if (file_exists($crate_path)) {
            $data = file_get_contents($crate_path);
            return new Response($data, 200, ['Content-Type' => 'text/plain']);
        }

        throw new NotFoundHttpException("invalid crate url");
    }
}
