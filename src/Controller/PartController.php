<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PartController extends AbstractController
{
    #[Route('/parts', name: 'app_part_index')]
    public function index(): Response
    {
        return $this->render('part/index.html.twig', [
            'controller_name' => 'PartController',
        ]);
    }
}
