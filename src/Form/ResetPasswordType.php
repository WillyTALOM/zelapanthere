<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Rollerworks\Component\PasswordStrength\Validator\Constraints\PasswordStrength;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'plainPassword',
                RepeatedType::class,
                [
                    'type' => PasswordType::class,
                    'invalid_message' => 'les deux mots de passe ne correspondent pas',
                    // instead of being set onto the object directly,
                    // this is read and encoded in the controller
                    'mapped' => false,
                    'attr' => ['autocomplete' => 'new-password'],
                    'first_options' => [
                        'label' => 'Nouveau mot de passe',
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
                        'label' => 'Répétez le mot de passe',
                        'constraints' => [
                            new NotBlank([
                                'message' => 'Merci de confirmer le mot de passe',
                            ])
                        ]
                    ]

                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class
        ]);
    }
}
