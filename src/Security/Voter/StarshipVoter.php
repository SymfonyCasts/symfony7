<?php

namespace App\Security\Voter;

use App\Entity\Starship;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

final class StarshipVoter extends Voter
{
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    public function __construct(
        private AccessDecisionManagerInterface $accessDecisionManager,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE])
            && $subject instanceof Starship;
    }

    /**
     * @param Starship $subject
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        if ($this->accessDecisionManager->decide($token, ['ROLE_ADMIN'])) {
            return true;
        }

        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            $vote?->addReason('The user must be logged in to access this resource.');

            return false;
        }

        // ... (check conditions and return true to grant permission) ...
        return $subject->getId() === $user->getStarship()?->getId();
    }

    public function supportsType(string $subjectType): bool
    {
        return is_a($subjectType, Starship::class, true);
    }

    public function supportsAttribute(string $attribute): bool
    {
        return in_array($attribute, [self::EDIT, self::DELETE]);
    }
}
