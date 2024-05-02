<?php

namespace App\Security\Voter;

use App\Entity\Recipe;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class RecipeVoter extends Voter
{
    public const LIST_ALL = 'RECIPE_ALL';
    public const LIST = 'RECIPE_LIST';
    public const CREATE = 'RECIPE_CREATE';
    public const EDIT = 'RECIPE_EDIT';
    public const VIEW = 'RECIPE_VIEW';

    function __construct(
        private readonly Security $security
    )
    {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return 
            in_array($attribute, [self::LIST_ALL, self::LIST, self::CREATE]) ||
            (
                in_array($attribute, [self::EDIT, self::VIEW])
                && $subject instanceof \App\Entity\Recipe
            )
        ;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        // if the user is anonymous, do not grant access
        if (!$user instanceof UserInterface) {
            return false;
        }

        /* if (!$subject instanceof Recipe) {
            return false;
        } */

        // ... (check conditions and return true to grant permission) ...
        switch ($attribute) {
            case self::EDIT:
                /**
                 * @var User $user
                 */
                return $subject->getUser()->getId() === $user->getId();
            break;
            case self::LIST:
            case self::CREATE:
            case self::VIEW:
                // logic to determine if the user can VIEW
                // return true or false
                return true;
                /* return $this->security->isGranted('ROLE_ADMIN'); */
            break;
        }

        return false;
    }
}
