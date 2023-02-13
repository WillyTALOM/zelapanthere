<?php

namespace App\Controller;


use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index( ProductRepository $productRepository): Response
    {
        $bestSellers = $productRepository->findBestSellers();
        $products = $productRepository->findLastEight();        
        return $this->render('home/index.html.twig', [
            'products' => $products,
            'bestSellers' => $bestSellers
            
        ]);
    }
}
