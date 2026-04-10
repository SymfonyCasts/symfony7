<?php

namespace App\Security\Voter;

use App\Entity\Starship;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class StarshipVoter extends Voter
{
    public const string DELETE = 'STARSHIP_DELETE';
    public const string EDIT = 'STARSHIP_EDIT';
    public const string VIEW = 'STARSHIP_VIEW';

    public function __construct(
        private readonly AuthorizationCheckerInterface $authorizationChecker,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::DELETE, self::EDIT, self::VIEW])
            && $subject instanceof Starship;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {

        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof User) {
            $vote?->addReason('The user must be logged in to access this resource.');

            return false;
        }
        if (!$subject instanceof Starship) {
            $vote?->addReason('The subject must be an instance of Starship.');

            return false;

        }
        if ($this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN')) {
            return true;
        }

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::DELETE:
            case self::EDIT:
                return $subject->getCreatedBy() === $user;

            case self::VIEW:
                return true;
        }

        return false;
    }
}
