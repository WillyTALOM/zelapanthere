<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Knp\Component\Pager\PaginatorInterface;

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
       // $categorySlug = $categoryRepository->findOneBy(['slug'=> $slug]);
        return $this->render('base.html.twig', [
            // 'products' => $products,
            'categories' => $categories
        ]);
    }
    #[Route('/products/{category}', name: 'products_by_category')]
    public function getProduitsByCategory(string $category, CategoryRepository $categoryRepository, ProductRepository $productRepository, Request $request, PaginatorInterface $paginator ): Response
    {
        $category = $categoryRepository->findOneBy(['name' => $category]);
        $categories = $categoryRepository->findAll();
        $products = $productRepository->findBy(['category' => $category]);
        $data = $products;

        $products = $paginator->paginate(
             $data,
             $request->query->getInt('page', 1),
             12
         );

        return $this->render('product/productCategory.html.twig', [
            'products' => $products,
            'category' => $category,
            'categories' => $categories
        ]);
    }
}
