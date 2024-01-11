<?php

namespace App\Controller;

use App\Model\Starship;
use App\Repository\StarshipRepository;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class StarshipApiController extends AbstractController
{
    #[Route('/api/starships', methods: ['GET'])]
    public function getCollection(LoggerInterface $logger, StarshipRepository $repository): Response
    {
        $logger->info('Starship collection retrieved');
        $starships = $repository->findAll();

        return $this->json($starships);
    }

    #[Route('/api/starships/{id<\d+>}', methods: ['GET'])]
    public function get(int $id): Response
    {
        dd($id);
    }
}
