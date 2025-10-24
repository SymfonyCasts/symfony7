<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PartController extends AbstractController
{
    #[Route('/part', name: 'app_part')]
    public function index(): Response
    {
        return $this->render('part/index.html.twig', [
            'controller_name' => 'PartController',
        ]);
    }
}
