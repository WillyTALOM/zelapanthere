<?php

namespace App\Controller;

use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    #[Route('/account/{id}/orders', name: 'user_account_orders')]
    public function orders(OrderRepository $orderRepository, Request $request)
    {
        $orders = $orderRepository->findSuccessOrders($this->getUser());

        // $data = $orders;

        // $orders = $paginator->paginate(
        //     $data,
        //     $request->query->getInt('page', 1),
        //     10
        // );

        return $this->render('account/orders.html.twig', [
            'orders' => $orders
        ]);
    }

    #[Route('/account/orders/{reference}', name: 'user_account_order_details')]
    public function show($reference, OrderRepository $orderRepository, ProductRepository $productRepository)
    {
        $order = $orderRepository->findOneByReference($reference);

        $product = $productRepository->findOneByReference($reference);


        if (!$order || $order->getUser() != $this->getUser()) {
            return $this->redirectToRoute('user_account_orders');
        }

        return $this->render('account/order_details.html.twig', [
            'product' => $product,
            'order' => $order,


        ]);
    }
}
