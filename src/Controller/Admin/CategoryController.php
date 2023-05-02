<?php

namespace App\Controller\Admin;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{
    #[Route('/admin1025/category', name: 'admin_category')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        $categories = $categoryRepository->findAll();
        return $this->render('Admin/category/categoryAdmin.html.twig', [
            'categories' =>  $categories,
        ]);
    }

    #[Route('/admin1025/category/create', name: 'create_category')]
    public function create(Request $request, CategoryRepository $categoryRepository, ManagerRegistry $managerRegistry): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isvalid()) {
            $categories = $categoryRepository->findAll();
            $categoryNames = [];

            foreach ($categories as $categorie) {
                $categoryNames[] = $categorie->getName();
            }

             $img = $form['image']->getData();

             if($img){
                $extensionImg = $img->guessExtension();
                $nomImg = time() . '-1.'.$extensionImg;
                $img->move($this->getParameter('category_image_dir'), $nomImg);
                $category->setImage($nomImg);
             }
            // if ($img === null) {
            //     $this->addFlash('danger', 'Pas d\'image trouvée');
            //     return $this->redirectToRoute('admin_category');
            // }
            

            
            $slugger = new AsciiSlugger();
            $category->setSlug(strtolower($slugger->slug($form['name']->getData())));



            $manager = $managerRegistry->getManager();
            $manager->persist($category);
            $manager->flush();

            $this->addFlash('success', 'La categorie a bien été créé');
            return $this->redirectToRoute('admin_category');
        }

        return $this->render('Admin/category/form.html.twig', [
            'categoryForm' => $form->createView()
        ]);
    }

    #[Route('/admin1025/category/update/{id}', name: 'category_update')]
    public function update(Category $category, CategoryRepository $categoryRepository, Request $request, ManagerRegistry $managerRegistry, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(CategoryType::class,$category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $categories =  $categoryRepository->findAll();
            $categoryName = [];

            foreach ($categories as $categorie) {

                $categoryName[] = $categorie->getName();
            }

            $img = $form['image']->getData();

            if ($img !== null) {
                $oldImg = $category->getImage();
                $oldImgPath = $this->getParameter('category_image_dir') . '/' . $oldImg;

                if (file_exists($oldImgPath)) {
                    unlink($oldImgPath);
                }

                $extensionImg = $img->guessExtension();
                $nomImg = time() . '-1.' . $extensionImg;
                $img->move($this->getParameter('category_image_dir'), $nomImg);
                $category->setImage($nomImg);
            }
            
            $categorie->setSlug(strtolower($slugger->slug($form['name']->getData())));

            $manager = $managerRegistry->getManager();
            $manager->persist($category);
            $manager->flush();

            $this->addFlash('success', 'La category a bien été modifié');
            return $this->redirectToRoute('admin_category');
        }
        return $this->render('Admin/category/form.html.twig', [
            'categoryForm' => $form->createView()
        ]);
    }
    

    #[Route('/admin1025/category/delete/{id}', name: 'category_delete')]
    public function delete(Category $category, ManagerRegistry $managerRegistry): Response
    {
        $imagePath = $this->getParameter('carrier_image_dir') . '/' . $category->getImage();
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }

        $manager = $managerRegistry->getManager();
        $manager->remove($category);
        $manager->flush();
        $this->addFlash('success', 'La categorie a bein été supprimé');
        return $this->redirectToRoute('admin_category');
    }
}
