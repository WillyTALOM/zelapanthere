<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('firstName', TextType::class, [
            'attr' => [
                'maxLength' => 100
            ]
        ])
        ->add('lastName', TextType::class, [
            'attr' => [
                'maxLength' => 100
            ]
        ])
        ->add('phone', NumberType::class, [
            'attr' => [
                'maxLength' => 100
            ]
        ])
        ->add('company', TextType::class, [
            'required' => false,
            'attr' => [
                'maxLenght' => 100
            ]
        ])
        ->add('email', EmailType::class, [
            'attr' => [
                'maxLenght' => 100
            ]
        ])

        ->add('subject', ChoiceType::class, [
            'choices' => [
                '---choix---' => '',
                'commande' => 'commande',
                'livraison' => 'livraison',
                'Votre compte' => 'compte',
                'signaler un bug' => 'problème',
                'SAV' => 'SAV',
                'autre' => 'autre'
            ]
        ])

        ->add('message', TextareaType::class, [
            'attr' => [
                'minLenght' => 50,
                'maxLenght' => 3000
            ],
            'help' => '3000 caractères maximum'
        ])

        ->add('attachment', FileType::class, [
            'required' => false,
            'help' => 'image ou document PDF',
            'constraints' => [
                new File([
                    'maxSize' => '2M',
                    'maxSizeMessage' => 'Le fichier est trop volumineux ({{ size }}) ({{ suffix }}). La taille maximale autorisée est de ({{ limit }}) ({{ suffix }}) .',
                    'mimeTypes' => [
                        'image/*',
                        'application/pdf'
                    ],
                    'mimeTypesMessage' => 'le type de fichier est invalide ({{ type}}). Les types autorisés sont : ({{ type }})'


                ])
            ]
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
