<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Carrier;
use App\Form\AddressType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class CartValidationInfoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', EmailType::class, [
            'mapped' => false,
            'attr' => [
                'maxLength' => 180
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
        // ->add('addresses', EntityType::class, [
        //     'class' => Address::class,
        //     // 'choice_label' => 'name',
        //      'expanded' => true
        // ])
        ->add('carrier', EntityType::class, [
            'class' => Carrier::class,
            'choice_label' => function(Carrier $carrier) {
                return $carrier->getName() . ' (' . number_format($carrier->getPrice(), 2, ',', ' ') . ' €)';
            }
        ])
        ->add('address')
        ->add('additional',  null, ['required' => false])
        ->add('zip')
        ->add('city')
        ->add('country', CountryType::class)
        ->add('payment', ChoiceType::class, [
            'choices' => [
                // 'Paypal' => 'paypal',
                'Carte bancaire'  => 'carte bancaire'
            ],
            'label' => false,
            'required' => true,
            'multiple' => false,
            'expanded' => true
       ]) 
            
        ;
        
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // 
        ]);
    }
}
