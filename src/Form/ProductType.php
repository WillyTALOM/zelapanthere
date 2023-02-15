<?php

namespace App\Form;

use App\Entity\Product;
use App\Form\ImageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => [
                    'maxLenght' => 100
                ]
            ])
            ->add('abstract', TextareaType::class, [
                'attr' => [
                    'max length' => 65535
                ]
            ])
            ->add('description', TextareaType::class, [
                'attr' => [
                    'max length' => 65535
                ]
            ])
            ->add('quantity', IntegerType::class, [
                'attr' => [
                    'min' => 0,
                    'max' => 99999,
                    'step' => 1
                ]
            ])
            ->add('price', NumberType::class, [
                'attr' => [
                    'min' => 0,
                    'max' => 9999.99,
                    'step' => 0.01
                ]
            ])
            ->add('reference' , TextType::class, [
                'attr' => [
                    'maxLength' => 100,

                ]
            ])
            ->add('reduction', NumberType::class, [
                'attr' => [
                    'min' => 0,
                    'max' => 9999.99,
                    'step' => 0.01
                ]
            ])

            ->add('images', FileType::class, [
                'required' => false,
                'label' => false,
                'multiple' => true,
                'mapped' => false,
                'help' => 'png, jpg, jpeg, jp2 ou webp - 5 Mo maximum',
                
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
