<?php

namespace App\Controller\Admin;

use Twig\Environment;
use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]

#[Route('admin/categories', name: "admin.categories.", host: "localhost")]

class CategoryController extends AbstractController
{

    private $request;

    function __construct(
        private readonly Environment $twig,
        private readonly RequestStack $requestStack,
        private readonly EntityManagerInterface $em,
        private readonly FormFactoryInterface $formFactory,
        private readonly CategoryRepository $categoryRepository,
    )
    {
        $this->request = $this->requestStack->getCurrentRequest();
    }

    /* #[IsGranted('ROLE_ADMIN')] */
    #[Route('/', name: 'index', methods: ['GET'])]
    function index() : Response
    {
        /* $this->denyAccessUnlessGranted('ROLE_ADMIN'); */

        return $this->render('admin/category/index.html.twig', [
            'categories' => $this->categoryRepository->findAll()
        ]);
    }


    #[Route(
        '/{slug}-{category}',
        name: 'show',
        methods: ['GET'],
        requirements: ['slug' => '[A-Za-z0-9-]+', 'category' => Requirement::DIGITS]
    )]
    function show (string $slug, Category $category) : Response
    {
        if ($slug !== $category->getSlug())
        return $this->redirectToRoute('admin.categories.show', ['slug' => $category->getSlug(), 'category' => $category->getId()]);

        return $this->render('admin/category/show.html.twig', [
            'category' => $category,
            'recipes' => $category->getRecipes()
        ]);
    }


    #[Route('/create', name: 'create', methods: ['GET', 'POST'])]
    function create () : Response
    {
        $category = new Category();
        $category->setName('Catégorie de test');
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setCreatedAt(new \DateTimeImmutable());
            $category->setUpdatedAt(new \DateTimeImmutable());
            $this->em->persist($category);
            $this->em->flush();
            $this->addFlash('success', 'La catégorie a été créee avec succès');
            return $this->redirectToRoute('admin.categories.index');
        }

        return $this->render('admin/category/create.html.twig', [
            'form' => $form,
            'category' => $category
        ]);
    }


    #[Route(
        '/{category}/edit',
        name: 'edit',
        methods: ['GET', 'POST'],
        requirements: ['slug' => '[A-Za-z0-9-]+', 'category' => Requirement::DIGITS]
    )]
    function edit (Category $category) : Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($this->request);

        if ($form->isSubmitted() && $form->isValid()) {
            $category->setUpdatedAt(new \DateTimeImmutable());
            $this->em->flush();
            $this->addFlash('success', 'La catégorie a été éditée avec succès');
            return $this->redirectToRoute('admin.categories.index');
        }

        return $this->render('admin/category/edit.html.twig', [
            'form' => $form,
            'category' => $category
        ]);
    }


    #[Route(
        '/{category}',
        name: 'delete',
        methods: ['DELETE'],
        requirements: ['slug' => '[A-Za-z0-9-]+', 'category' => Requirement::DIGITS]
    )]
    function delete (Category $category) : Response
    {
        $this->em->remove($category);
        $this->em->flush();
        $this->addFlash('success', 'La catégorie a été supprimée avec succès');
        return $this->redirectToRoute('admin.categories.index');
    }

}
