<?php

namespace App\Controller\Admin;

use App\Entity\Commentaire;
use App\Repository\CommentaireRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AdminCommentsController extends AbstractController
{
    #[Route('/admin1025/comments', name: 'admin_comments')]
    public function index(CommentaireRepository $commentaireRepository): Response
    {
        $comments = $commentaireRepository->findAll();
        return $this->render('Admin/comments/commentAdmin.html.twig', [
            'comments' =>  $comments,
        ]);
    }


    #[Route('/admin1025/comment/delete/{id}', name: 'comment_delete')]
    public function delete(Commentaire $commentaire, ManagerRegistry $managerRegistry): Response
    {

        $manager = $managerRegistry->getManager();
        $manager->remove($commentaire);
        $manager->flush();
        $this->addFlash('success', 'Le commentaire a bein été supprimé');
        return $this->redirectToRoute('admin_comments');
    }
}
