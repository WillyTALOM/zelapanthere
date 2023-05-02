<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'category')]
    public function getCategory( CategoryRepository $categoryRepository, ProductRepository $productRepository,): Response
    {
        $categories = $categoryRepository->findAll();
        // $products = $productRepository->findBy(['category' => $category]);

        // $data = $products;

        // $products = $paginator->paginate(
        //     $data,
        //     $request->query->getInt('page', 1),
        //     12
        // );

        return $this->render('base.html.twig', [
            // 'products' => $products,
            'categories' => $categories
        ]);
    }
}
