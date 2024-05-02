<?php

namespace App\Controller;

use App\Demo;
use App\Entity\Recipe;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    function __construct(
        private readonly Security $security,
    )
    {
    }

    #[Route('/demo', name: 'demo', methods: ['GET'])]
    function demo(ValidatorInterface $validator, Demo $demo) : void
    {
        dd($demo);
        $recipe = new Recipe();
        $errors = $validator->validate($recipe);
        dd((string)$errors);
        dd($validator);
    }

    #[Route('/home', name: 'home')]
    function index(): Response
    {
        /* dd($this->getUser());
        dd($this->security->getUser());
        dd($this->security->getToken()); */
        return $this->render('home/index.html.twig');
    }
}
