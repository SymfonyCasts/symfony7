<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

final class LastUserLoginListener
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[AsEventListener]
    public function onLoginSuccessEvent(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!$user instanceof User) {
            throw new \LogicException(sprintf('Expected an instance of %s.', User::class));
        }

        $user->setLastLoginAt(new \DateTimeImmutable());

        $this->entityManager->flush();
    }
}
