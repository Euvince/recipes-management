<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsersFixtures extends Fixture
{

    private const ADMIN = 'USER_ADMIN';

    function __construct(
        private readonly UserPasswordHasherInterface $hasher
    )
    {
    }

    public function load(ObjectManager $manager): void
    {

        $user = (new User())
            ->setUsername('John DOE')
            ->setEmail('john@doe.fr')
            ->setRoles(['ROLE_USER'])
            ->setIsVerified(true)
        ;
        $user->setPassword($this->hasher->hashPassword($user, '0000'));
        $this->addReference(self::ADMIN, $user);
        $manager->persist($user);

        for ((int) $i = 1; $i <= 10; $i++) {
            $user = new User();
            $user->setUsername("user{$i}")
                ->setEmail("user{$i}@gmail.com")
                ->setPassword($this->hasher->hashPassword($user, '0000'))
                ->setRoles(['ROLE_USER'])
                ->setIsVerified(true)
            ;
            $this->addReference("USER{$i}", $user);
        }

        $manager->flush();
    }
}
