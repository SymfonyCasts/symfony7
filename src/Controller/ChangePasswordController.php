<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class ChangePasswordController extends AbstractController
{
    #[Route('/change-password', name: 'app_change_password')]
    public function __invoke(
        #[CurrentUser]
        User $user,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        Request $request,
    ): Response {
        $form = $this->createForm(ChangePasswordFormType::class, options: ['require_current' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $form->get('plainPassword')->getData();

            $user->setPassword($hasher->hashPassword($user, $password));
            $em->flush();
            $this->addFlash('success', 'Your password has been changed!');

            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('security/change_password.html.twig', [
            'form' => $form,
        ]);
    }
}
