<?php

namespace App\Form;

use App\DTO\ContactDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type as TT;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TT\TextType::class, [
                'label' => 'Nom',
            ])
            ->add('email', TT\EmailType::class, [
                'label' => 'Email',
                /* 'constraints' => new Assert\Sequentially([
                    new Assert\Email()
                ]) */
            ])
            ->add('message', TT\TextareaType::class, [
                'label' => 'Message',
                /* 'constraints' => new Assert\Sequentially([
                    new Assert\Length(min: 10)
                ]) */
            ])
            ->add('service', TT\ChoiceType::class, [
                'label' => 'Service',
                'choices' => [
                    'Support' => 'support@demo.fr',
                    'Finances' => 'finances@demo.fr',
                    'Comptabilité' => 'compta@demo.fr',
                ]
            ])
            ->add('save', TT\SubmitType::class, [
                'label' => 'Envoyer'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactDTO::class,
            'validation_groups' => ['Default']
        ]);
    }
}
