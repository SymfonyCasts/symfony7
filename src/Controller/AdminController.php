<?php

namespace App\Controller;

use App\Entity\StarshipPart;
use App\Form\StarshipPartType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/starship-part/new', name: 'app_admin_starship_part_new', methods: ['GET', 'POST'])]
    public function newStarshipPart(
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $form = $this->createForm(StarshipPartType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var StarshipPart $part */
            $part = $form->getData();
            $entityManager->persist($part);
            $entityManager->flush();

            $this->addFlash('success', sprintf('The part "%s" was successfully created.', $part->getName()));

            /** @var SubmitButton $createAndAddNewBtn */
            $createAndAddNewBtn = $form->get('createAndAddNew');
            if ($createAndAddNewBtn->isClicked()) {
                return $this->redirectToRoute('app_admin_starship_part_new');
            }

            return $this->redirectToRoute('app_part_index');
        }

        return $this->render('admin/starship-part/new.html.twig', [
            'form' => $form,
        ]);
    }
}
