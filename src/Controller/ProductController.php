<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'product_list', methods: ['GET'])]
    public function index(ProductRepository $productRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $products = $productRepository->findAll(); 
        $data = $products;

        $products = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            12
        );
        return $this->render('product/productCategory.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/{slug}', name: 'product_show', methods: ['GET'])]
    public function show($slug, ProductRepository $productRepository): Response
    {
        $product = $productRepository->findOneBy(['slug'=> $slug]);
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    // #[Route('/products/{category}', name: 'products_by_category')]
    // public function getProduitsBySexe(string $category, CategoryRepository $categoryRepository, ProductRepository $productRepository, Request $request): Response
    // {
    //     $category = $categoryRepository->findOneBy(['name' => $category]);
    //     $products = $productRepository->findBy(['category' => $category]);

    //     // $data = $products;

    //     // $products = $paginator->paginate(
    //     //     $data,
    //     //     $request->query->getInt('page', 1),
    //     //     12
    //     // );

    //     return $this->render('product/sexe.html.twig', [
    //         'products' => $products,
    //         'category' => $category
    //     ]);
    // }

    
}
