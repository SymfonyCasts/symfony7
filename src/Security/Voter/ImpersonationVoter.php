<?php

namespace App\Security\Voter;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class ImpersonationVoter extends Voter
{
    public function __construct(private Security $security)
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return 'CAN_IMPERSONATE' === $attribute && $subject instanceof User;
    }

    /**
     * @param User $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if (!$currentUser = $token->getUser()) {
            return false;
        }

        if ($currentUser->getUserIdentifier() === $subject->getUserIdentifier()) {
            return false;
        }

        if (in_array('ROLE_SUPER_ADMIN', $token->getRoleNames(), true)) {
            return true;
        }

        return !$this->security->isGrantedForUser($subject, 'ROLE_ADMIN');
    }
}
