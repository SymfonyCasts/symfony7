<?php

namespace App\Controller;

use App\Entity\Starship;
use App\Entity\User;
use App\Form\StarshipType;
use App\Repository\StarshipRepository;
use App\Security\Voter\StarshipVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/starship')]
final class StarshipAdminController extends AbstractController
{
    #[Route(name: 'app_starship_admin_index', methods: ['GET'])]
    public function index(StarshipRepository $starshipRepository): Response
    {
        return $this->render('starship_admin/index.html.twig', [
            'starships' => $starshipRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_starship_admin_new', methods: ['GET', 'POST'])]
    public function new(
        #[CurrentUser] User $user,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
//        $user = $this->getUser();
        $starship = new Starship();
        $form = $this->createForm(StarshipType::class, $starship);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($starship);
            $starship->setCreatedBy($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_starship_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('starship_admin/new.html.twig', [
            'starship' => $starship,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_starship_admin_show', methods: ['GET'])]
    public function show(Starship $starship): Response
    {
        return $this->render('starship_admin/show.html.twig', [
            'starship' => $starship,
        ]);
    }

    #[IsGranted(StarshipVoter::EDIT, 'starship', 'Access denied.', Response::HTTP_FORBIDDEN)]
    #[Route('/{id}/edit', name: 'app_starship_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Starship $starship, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(StarshipType::class, $starship);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_starship_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('starship_admin/edit.html.twig', [
            'starship' => $starship,
            'form' => $form,
        ]);
    }

    #[IsGranted(StarshipVoter::DELETE, 'starship', 'Access denied.', Response::HTTP_FORBIDDEN)]
    #[Route('/{id}', name: 'app_starship_admin_delete', methods: ['POST'])]
    public function delete(Request $request, Starship $starship, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$starship->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($starship);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_starship_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}
