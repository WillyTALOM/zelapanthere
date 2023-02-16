<?php

namespace App\Controller\Admin;

use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    #[Route('/admin1025/orders', name: 'admin_orders')]
    public function index(OrderRepository $orderRepository): Response
    {
        return $this->render('Admin/order/orderList.html.twig', [
            'orders' => $orderRepository->findAll()
        ]);
    }
}
