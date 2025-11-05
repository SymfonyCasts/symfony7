<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/starship-part/new', name: 'app_admin_starship_part_new', methods: ['GET', 'POST'])]
    public function newStarshipPart(): Response {
        return $this->render('admin/starship-part/new.html.twig');
    }
}
