<?php

namespace App\Controller;

use App\Service\CrateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[Route(path: '/', name: 'homepage')]
    public function index(CrateService $crateService): Response
    {
        $indices = $crateService->getAllIndices();
        return $this->render('default/index.html.twig', [
            'indices' => $indices,
        ]);
    }

    #[Route('/me', name: 'me')]
    public function me(): Response
    {
        return new Response("No tokens available. You need to set them manually in your tokens.json file.");
    }

    #[Route('/tokens.json', name: 'token_json')]
    public function tokens(): Response
    {
        return new Response("", Response::HTTP_I_AM_A_TEAPOT);
    }

    #[Route('/config.json', name: 'config_json')]
    public function config_json(Request $request): JsonResponse
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
