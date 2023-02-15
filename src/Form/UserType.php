<?php

namespace App\Form;

use App\Entity\User;
use App\Form\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Rollerworks\Component\PasswordStrength\Validator\Constraints\PasswordStrength;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', EmailType::class, [
            'attr' => [
                'maxLength' => 180
            ]
        ])
        ->add('roles', CollectionType::class, [
            'entry_type'   => ChoiceType::class,
            'entry_options'  => [
                'label' => false,
                'choices' => [
                    'Admin' => 'ROLE_SUPER_ADMIN',
                    'Utilisateur' => 'ROLE_USER',
                    
                ],
            ],
        ])
        ->add('password', RepeatedType::class, [
            'type' => PasswordType::class,
            'invalid_message' => 'les deux mots de passe ne correspondent pas',
            'mapped' => false,
            'attr' => ['autocomplete' => 'new-password'],
            'first_options' => [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    // new Length([
                    //     'min' => 6,
                    //     'minMessage' => 'Your password should be at least {{ limit }} characters',
                    //     // max length allowed by Symfony for security reasons
                    //     'max' => 4096,
                    // ]),
                    new PasswordStrength([
                        'minLength' => 8,
                        'tooShortMessage' => 'Le mot de passe doit contenir au moins {{length}} caractères.',
                        'minStrength' => 4,
                        'message' => 'Le mot de passe doit contenir au moins une lettre minuscule, une lettre majiscule, un chiffre et un caratère spécial'
                    ])
                ],

            ],
            'second_options' => [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de confirmer le mot de passe',
                    ])
                ]
            ]
        ])
        ->add('last_name', TextType::class, [
            'attr' => [
                'maxLenght' => 100
            ]
        ])
        ->add('first_name', TextType::class, [
            'attr' => [
                'maxLenght' => 100
            ]
        ])
        ->add('phone', TextType::class, [
            'attr' => [
                'maxLength' => 15
            ]
        ])
        // ->add('orders', CollectionType::class, [
        //     'entry_type' => OrderType::class,
        //     'entry_options' => [
        //         'label' => false
        //     ],
        //     'by_reference' => false,
        //     'allow_add' => true,
        //     'allow_delete' => true

        // ])
        ->add('addresses', CollectionType::class, [
            'entry_type' => AddressType::class,
            'entry_options' => [
                'label' => false
            ],
            'by_reference' => false,
            'allow_add' => true,
            'allow_delete' => true

        ])
        ->add('isVerified');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
