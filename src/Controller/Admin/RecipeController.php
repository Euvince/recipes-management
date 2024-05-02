<?php

namespace App\Controller\Admin;

use Twig\Environment;
use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Message\RecipePDFMessage;
use App\Repository\RecipeRepository;
use App\Repository\CategoryRepository;
use App\Security\Voter\RecipeVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

/* #[IsGranted('ROLE_ADMIN')] */

#[Route('admin/recipes', name: "admin.recipes.", host: "localhost")]

class RecipeController extends AbstractController
{

    private $request;

    function __construct(
        private readonly Environment $twig,
        private readonly Security $security,
        private readonly RequestStack $requestStack,
        private readonly EntityManagerInterface $em,
        private readonly FormFactoryInterface $formFactory,
        private readonly RecipeRepository $recipeRepository,
    )
    {
        $this->request = $this->requestStack->getCurrentRequest();
    }


    #[Route('/', name: 'index', methods: ['GET'])]
    #[IsGranted(RecipeVoter::LIST)]
    function index() : Response
    {
        /* $this->denyAccessUnlessGranted('ROLE_ADMIN'); */

        $page = $this->request->get('page', 1);
        /**
         * @var User $user
         */
        $user = $this->security->getUser();
        $userId = $user->getId();
        $canListAll = $this->security->isGranted(RecipeVoter::LIST_ALL);
        return $this->render('admin/recipe/index.html.twig', [
            'recipes' => $this->recipeRepository->paginateRecipes($page, 10, $canListAll ? null : $userId)
        ]);
    }


    #[Route(
        '/{slug}-{recipe}',
        name: 'show',
        methods: ['GET'],
        requirements: ['slug' => '[A-Za-z0-9-]+', 'recipe' => Requirement::DIGITS]
    )]
    function show (string $slug, Recipe $recipe) : Response
    {
        if ($slug !== $recipe->getSlug())
        return $this->redirectToRoute('admin.recipes.show', ['slug' => $recipe->getSlug(), 'recipe' => $recipe->getId()]);

        return $this->render('admin/recipe/show.html.twig', [
            'recipe' => $recipe
        ]);
    }


    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    function create (CategoryRepository $categoryRepository) : Response
    {
        $recipe = new Recipe();

        $recipe->setTitle('Nouvelle recette');
        $recipe->setSlug('nouvelle-recette');
        $recipe->setContent('Création de recette pour test');
        $recipe->setDuration(12);
        $recipe->setCategory($categoryRepository->findOneBy(['slug' => 'plat-principal']));

        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {

            /**
             * @var UploadedFile $file
             */
            /* $file = $form->get('thumbnailFile')->getData();
            dd($file->getClientOriginalName(), $file->getClientOriginalExtension());
            $fileName = $file->getClientOriginalName();
            $file->move($this->getParameter('kernel.project_dir') . '/public/images/recipes/',  $fileName);
            $recipe->setThumbnail($fileName); */

            $recipe->setCreatedAt(new \DateTimeImmutable());
            $recipe->setUpdatedAt(new \DateTimeImmutable());
            $this->em->persist($recipe);
            $this->em->flush();
            $this->addFlash('success', 'La recette a été créee avec succès');
            return $this->redirectToRoute('admin.recipes.index');
        }

        return $this->render('admin/recipe/create.html.twig', [
            'form' => $form,
            'recipe' => $recipe
        ]);
    }


    #[Route(
        '/{recipe}/edit',
        name: 'edit',
        methods: ['GET', 'POST'],
        requirements: ['slug' => '[A-Za-z0-9-]+', 'recipe' => Requirement::DIGITS]
    )]
    #[IsGranted(RecipeVoter::EDIT, subject: 'recipe')]
    function edit (Recipe $recipe, MessageBusInterface $messageBus) : Response
    {
        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {

            /**
             * @var UploadedFile $file
             */
            /* $file = $form->get('thumbnailFile')->getData();
            dd($file->getClientOriginalName(), $file->getClientOriginalExtension());
            $fileName = $file->getClientOriginalName();
            $file->move($this->getParameter('kernel.project_dir') . '/public/images/recipes/',  $fileName);
            $recipe->setThumbnail($fileName); */
            $messageBus->dispatch(new RecipePDFMessage($recipe->getId())/* , [new DelayStamp(2)] */);
            $recipe->setUpdatedAt(new \DateTimeImmutable());
            $this->em->flush();
            $this->addFlash('success', 'La recette a été éditée avec succès');
            return $this->redirectToRoute('admin.recipes.index');
        }

        return $this->render('admin/recipe/edit.html.twig', [
            'form' => $form,
            'recipe' => $recipe
        ]);
    }


    #[Route(
        '/{recipe}',
        name: 'delete',
        methods: ['DELETE'],
        requirements: ['slug' => '[A-Za-z0-9-]+', 'recipe' => Requirement::DIGITS],
    )]
    function delete (Recipe $recipe) : Response
    {
        $this->em->remove($recipe);
        $this->em->flush();
        $this->addFlash('success', 'La recette a été supprimée avec succès');
        return $this->redirectToRoute('admin.recipes.index');
    }

}
