<?php

namespace App\Controller\Admin;


use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminHomeController extends AbstractController
{
    #[Route('/admin1025', name: 'admin_home')]
    public function index( ): Response
    {       
        return $this->render('Admin/index.html.twig', [
            
        ]);
    }
}
