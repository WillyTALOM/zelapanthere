<?php

namespace App\DataFixtures;

use App\Entity\Image;
use Faker\Factory;
use App\Entity\Product;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\String\Slugger\AsciiSlugger;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        
        $slugger = new AsciiSlugger();
        $faker = Factory::create();

        for ($i = 1; $i <= 20; $i++){
            
        $image = new Image();
        $image->setName($faker->imageUrl(640, 480, 'animals', true));
        
        $manager->persist($image);


        $product = new Product();
        $product
            ->setName($faker->text(35))
            ->setSlug(strtolower($slugger->slug($product->getName())))
            ->setAbstract($faker->text(100))
            ->setDescription($faker->text(100))
            ->setQuantity($faker->numberBetween(0,100))
            ->setPrice($faker->randomFloat(2, 4, 200))
            ->setCreatedAt(new \DateTimeImmutable())
            ->setReference($faker->text(10))
            ->setReduction(1)
            ->setPriceSold($product->getPrice()- $product->getReduction())
            ->addImage($image);
            $manager->persist($product);
        }

        
 
        $manager->flush();
    }
}
