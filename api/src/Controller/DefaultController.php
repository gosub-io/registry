<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route('/me', name: 'me')]
    public function me(): Response
    {
        return new Response("No tokens available.");
    }

    #[Route('/config.json', name: 'config_json')]
    public function index(Request $request): JsonResponse
    {
        $url = $request->getScheme() . '://' . $request->getHost();
        if ($request->getPort() !== 80) {
            $url .= ":{$request->getPort()}";
        }

        return $this->json([
            'dl' => "{$url}/crates/{crate}-{version}.crate",
            'api' => "{$url}",
        ]);
    }
}
