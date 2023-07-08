<?php

namespace App\Controller;


use DateTimeImmutable;
use App\Entity\Product;
use App\Form\ProductType;
use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use App\Repository\CommentaireRepository;
use Doctrine\Persistence\ManagerRegistry;
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

    // #[Route('/{slug}', name: 'product_show')]
    // public function show($slug, ProductRepository $productRepository, Request $request,ManagerRegistry $managerRegistry, CommentaireRepository $commentaireRepository): Response
    // {
    //     // $productId = $productRepository->findBy($id);
    //     $comment = new Commentaire;   
    //     $manager= $managerRegistry->getManager();
    //     $form = $this->createForm(CommentaireType::class,$comment);
    //     $form->handleRequest($request);
    //     $product = $productRepository->findOneBy(['slug'=> $slug]);
    //     $content = $form['content']->getData();
    //     $user = $this->getUser();
    //     if($form->isSubmitted() && $form->isValid()){
            
    //         $comment->setAuthor($user);
    //         $comment->setProduct($product);
    //         $comment->setCreatedAt(new DateTimeImmutable());
    //         $comment->setContent($content);

    //         $manager->persist($comment);
    //         $manager->flush();

    //         $this->addFlash('success', 'Votre commentaire a été envoyé');
        
    //     }
        
    //     return $this->render('product/show.html.twig', [
    //         'product' => $product,
    //         'commentForm' => $form->createView(),
    //         // 'comments' => $commentaireRepository->findBy(['product'=>$productId])
    //     ]);
    // }
    #[Route('/{id}', name: 'product_show')]
    public function showid($id, ProductRepository $productRepository, Request $request,ManagerRegistry $managerRegistry, CommentaireRepository $commentaireRepository): Response
    {
        $productId = $productRepository->find($id);
        $comment = new Commentaire;   
        $manager= $managerRegistry->getManager();
        $form = $this->createForm(CommentaireType::class,$comment);
        $form->handleRequest($request);
        $content = $form['content']->getData();
        $user = $this->getUser();
        if($form->isSubmitted() && $form->isValid()){
            
            $comment->setAuthor($user);
            $comment->setProduct($productId);
            $comment->setCreatedAt(new DateTimeImmutable());
            $comment->setContent($content);

            $manager->persist($comment);
            $manager->flush();

            $this->addFlash('success', 'Votre commentaire a été envoyé');
        
        }
        
        return $this->render('product/show.html.twig', [
            'product' => $productRepository->find($productId),
            'commentForm' => $form->createView(),
            'comments' => $commentaireRepository->findBy(['Product'=>$productId])
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
