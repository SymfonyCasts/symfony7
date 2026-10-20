<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class SuperAdminVoter extends Voter
{
    private const EXCLUSIONS = [
        AuthenticatedVoter::IS_AUTHENTICATED,
        AuthenticatedVoter::IS_AUTHENTICATED_FULLY,
        AuthenticatedVoter::IS_AUTHENTICATED_REMEMBERED,
        AuthenticatedVoter::IS_IMPERSONATOR,
        AuthenticatedVoter::IS_REMEMBERED,
        AuthenticatedVoter::PUBLIC_ACCESS,
        'CAN_IMPERSONATE',
    ];

    protected function supports(string $attribute, mixed $subject): bool
    {
        return !in_array($attribute, self::EXCLUSIONS, true);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        return in_array('ROLE_SUPER_ADMIN', $token->getRoleNames(), true);
    }
}
