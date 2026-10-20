<?php

namespace App\EventListener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

final class LastLoginListener
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {
    }

    #[AsEventListener]
    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if (!$user instanceof User) {
            return;
        }

        $user->setLastLogin(new \DateTimeImmutable('now'));
        $this->em->flush();
    }
}
