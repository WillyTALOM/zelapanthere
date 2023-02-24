<?php

namespace App\Form;

use App\Entity\OrderState;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class OrderStateType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('name', ChoiceType::class, [
            'choices' => [
                '---choix---' => '',
                'En préparation' => 'En préparation',
                'Livraison' => 'Livraison',
                'Livré' => 'Livré',
                'Attente paiement'=> 'Attente paiement',
                'Payé' => 'Payé',
            ]
        ])
        
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => OrderState::class,
        ]);
    }
}
