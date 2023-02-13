<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use DateTimeImmutable;
use App\Entity\Product;
use App\Form\ProductType;
use App\Service\PictureService;
use App\Repository\ProductRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ProductListController extends AbstractController
{
    #[Route('/admin1025/products', name: 'admin_product_list' , methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('Admin/product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/admin1025/product/{slug}', name: 'admin_product_show')]
    public function show($slug, ProductRepository $productRepository): Response
    {
        $produits = $productRepository->findAll();
        $product = $productRepository->findOneBy(['slug' => $slug]);
        return $this->render('Admin/product/show.html.twig', [
            'product' => $product,
            'products' => $produits,


        ]);
    }

    #[Route('/admin1025/create', name: 'admin_product_create')]
    public function create(ManagerRegistry $managerRegistry, Request $request, SluggerInterface $slugger, PictureService $pictureService): Response
    {
        $product = new Product();
        $manager= $managerRegistry->getManager();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $images = $form->get('images')->getData();

            foreach($images as $image){
                //on définit le dossier de destination
                $folder =  'products';

                //on appelle le service d'ajout
                $fichier = $pictureService->add($image, $folder, 300, 300);
                 $img = new Image();
                 $img->setName($fichier);
                 $product->addImage($img);

            }
            

            // $productDir = $this->getParameter('product_file_dir');
            
            // if(!file_exists($productDir)){
            //     mkdir($productDir);
            // }

            // $imageDir = $productDir . '/' . strtolower($slugger->slug($product->getName()));

            // if (!file_exists($imageDir )){
            //     mkdir($imageDir );
            // }

            // $infoImages = $form->get('images')->getData();
            
            // if ($infoImages) {
                
            //     foreach($infoImages as $image){
            
            //     $extensionImage = $image->guessExtension();

            //     $nomImage = strtolower($slugger->slug($product->getName())) . time() .  '.' . $extensionImage;
            //     $image->move($imageDir, $nomImage);
                
            //     $img = new Image();
            //     $img->setName($nomImage);
            //     $product->addImage($img);
              
            //      }
                 
                 
  
                  
  

            // }
           
            $product->setSlug(strtolower($slugger->slug($product->getName())));
            $product->setCreatedAt(new DateTimeImmutable());
            $product->setPriceSold($product->getPrice() * (1 - ($product->getReduction() / 100)));
            $manager->persist($product);
            $manager->flush();
           
            $this->addFlash('success', 'Le produit a bien été créé');
            return $this->redirectToRoute('admin_product_list', [], Response::HTTP_SEE_OTHER);
          
            
        }

        return $this->render('Admin/product/create.html.twig', [
            'product' => $product,
            'form' => $form->createView()
        ]);
    }


    #[Route('/admin1025/{id}/edit', name: 'admin_product_edit' , methods: ['GET', 'POST'])]
    public function edit(Product $product, Request $request, ProductRepository $productRepository): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $productRepository->save($product, true);

            return $this->redirectToRoute('admin_product_list', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('Admin/product/edit.html.twig', [
            'product' => $product,
            'form' => $form->createView()
        ]);
    }

    #[Route('/admin1025/{id}', name: 'admin_product_delete')]
    public function delete(Request $request, Product $product, ProductRepository $productRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $productRepository->remove($product, true);
        }

        return $this->redirectToRoute('admin_product_list', [], Response::HTTP_SEE_OTHER);
    }

}
