<?php

namespace App\Form;


use App\Entity\Address;
use App\Entity\Carrier;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CartValidationType extends AbstractType
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('lastName', TextType::class, [
            'attr' => [
                'maxLenght' => 100
            ]
           ])
            ->add('firstName', TextType::class, [
            'attr' => [
                'maxLenght' => 100
            ]
           ])
            ->add('billing_address', EntityType::class, [
                'class' => Address::class,
                'query_builder' => function(EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('a')
                        ->where('a.user = :val')
                        ->setParameter(':val', $this->security->getUser());
                },
                'choice_label' => function(Address $address) {
                    return $address->getAddress() . ' - ' . $address->getZip() . ' ' . $address->getCity();
                }
            ])
            ->add('delivery_address', EntityType::class, [
                'class' => Address::class,
                'query_builder' => function(EntityRepository $entityRepository) {
                    return $entityRepository->createQueryBuilder('a')
                        ->where('a.user = :val')
                        ->setParameter(':val', $this->security->getUser());
                },
                'choice_label' => function(Address $address) {
                    return $address->getAddress() . ' - ' . $address->getZip() . ' ' . $address->getCity();
                }
            ])
            ->add('carrier', EntityType::class, [
                'class' => Carrier::class,
                'choice_label' => function(Carrier $carrier) {
                    return $carrier->getName() . ' (' . number_format($carrier->getPrice(), 2, ',', ' ') . ' €)';
                }
            ])
           ->add('payment', ChoiceType::class, [
                'choices' => [
                    'Paypal' => 'paypal',
                    'Stripe'  => 'stripe'
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
            // Configure your form options here
        ]);
    }
}