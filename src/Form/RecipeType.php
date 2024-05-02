<?php

namespace App\Form;

use App\Entity\Recipe;
use App\Entity\Category;
use App\Form\FormListenerFactory;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\Image;

class RecipeType extends AbstractType
{

    function __construct(
        private readonly SluggerInterface $slugger,
        private readonly RequestStack $requestStack,
        private readonly FormListenerFactory $formListenerFactory
    )
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $routeName = $this->requestStack->getCurrentRequest()->attributes->get('_route');
        $buttonLabel = $routeName === 'admin.recipes.create' ? 'Soumettre' : 'Modifier';

        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                /* 'constraints' => new Assert\Sequentially([
                    new Assert\Length(min: 5),
                ]) */
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'required' => false,
                /* 'constraints' => new Assert\Sequentially([
                    new Assert\Length(min: 5),
                    new Assert\Regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', message: 'This value is not a valid slug')
                ]) */
            ])
            ->add('thumbnailFile', FileType::class, [
                'label' => 'Image de la recette',
                /* 'mapped' => false,
                'constraints' => [
                    new Image()
                ] */
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Contenu',
                /* 'constraints' => new Assert\Sequentially([
                    new Assert\Length(min: 10),
                ]) */
            ])
            ->add('duration', NumberType::class, [
                'label' => 'Durée (en minutes)',
                /* 'constraints' => new Assert\Sequentially([
                    new Assert\Positive()
                ]) */
            ])
            /* ->add('createdAt', null, [
                'widget' => 'single_text'
            ])
            ->add('updatedAt', null, [
                'widget' => 'single_text'
            ]) */
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'label' => 'Catégorie',
                'choice_label' => 'name',
                /* 'expanded' => true */
            ])
            ->add('quantities', CollectionType::class, [
                'entry_type' => QuantityType::class,
                'entry_options' => ['label' => false],
                'by_reference' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'attr' => [
                    'data-controller' => 'form-collection',
                    /* 'data-form-collection-add-label-value' => 'Ajouter un ingrédient',
                    'data-form-collection-delete-label-value' => 'Supprimer un ingrédient', */
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => $buttonLabel
            ])
            /* ->addEventListener(FormEvents::PRE_SUBMIT, $this->autoSlug(...))
            ->addEventListener(FormEvents::POST_SUBMIT, $this->attachTimestamps(...)) */
            ->addEventListener(FormEvents::PRE_SUBMIT, $this->formListenerFactory->autoSlug('title'))
            ->addEventListener(FormEvents::POST_SUBMIT, $this->formListenerFactory->attachTimestamps(Recipe::class))
        ;
    }

    private function autoSlug (PreSubmitEvent $event) : void {
        $data = $event->getData();
        if (empty($data['slug'])) {
            $data['slug'] = strtolower($this->slugger->slug($data['title']));
            $event->setData($data);
        }
    }

    private function attachTimestamps (PostSubmitEvent $event) : void {
        $data = $event->getData();
        if (!($data instanceof Recipe)) return;
        $data->setUpdatedAt(new \DateTimeImmutable());
        if (!$data->getId()) {
            $data->setCreatedAt(new \DateTimeImmutable());
        }
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recipe::class,
            'validation_groups' => ['Default', 'Extra']
        ]);
    }
}
