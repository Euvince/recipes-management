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
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints as Assert;

class CategoryType extends AbstractType
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
        $buttonLabel = $routeName === 'admin.categories.create' ? 'Soumettre' : 'Modifier';

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
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
            /* ->add('createdAt', null, [
                'widget' => 'single_text'
            ])
            ->add('updatedAt', null, [
                'widget' => 'single_text'
            ]) */
            /* ->add('recipes', EntityType::class, [
                'class' => Recipe::class,
                'label' => 'Recettes',
                'choice_label' => 'title',
                'by_reference' => false,
                'multiple' => true,
                'expanded' => true
            ]) */
            ->add('save', SubmitType::class, [
                'label' => $buttonLabel
            ])
            /* ->addEventListener(FormEvents::PRE_SUBMIT, $this->autoSlug(...))
            ->addEventListener(FormEvents::POST_SUBMIT, $this->attachTimestamps(...)) */
            ->addEventListener(FormEvents::PRE_SUBMIT, $this->formListenerFactory->autoSlug('name'))
            ->addEventListener(FormEvents::POST_SUBMIT, $this->formListenerFactory->attachTimestamps(Category::class))
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
        if (!($data instanceof Category)) return;
        $data->setUpdatedAt(new \DateTimeImmutable());
        if (!$data->getId()) {
            $data->setCreatedAt(new \DateTimeImmutable());
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
            'validation_groups' => ['Default']
        ]);
    }
}
