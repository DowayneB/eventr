<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends EventrController
{

    #[Route('/index', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }

    public function indewx(): Response
    {

        return new JsonResponse(
            $this->getSerializer()->serialize(
                $this->getUser(), 'json',
                null
            ),
            200,
            [],
            JSON_PRETTY_PRINT
        );
    }
}
