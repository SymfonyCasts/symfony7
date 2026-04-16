<?php

namespace App\Controller;

use App\Form\PartSearchType;
use App\Repository\StarshipPartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PartController extends AbstractController
{
    #[Route('/parts', name: 'app_part_index')]
    public function index(StarshipPartRepository $repository, Request $request,): Response
    {
        $searchForm = $this->createForm(PartSearchType::class);
        //$query = $request->query->getString('query');
        $query = null;
        $searchForm->handleRequest($request);
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
            $query = $searchForm->get('query')->getData();
        }

        $parts = $repository->findAllOrderedByPrice($query);

        return $this->render('part/index.html.twig', [
            'parts' => $parts,
            'searchForm' => $searchForm,
        ]);
    }
}
