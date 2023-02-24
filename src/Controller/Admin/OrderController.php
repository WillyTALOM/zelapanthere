<?php

namespace App\Controller\Admin;

use App\Entity\OrderDetails;
use App\Form\OrderStateType;
use App\Repository\OrderDetailsRepository;
use App\Repository\OrderRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    #[Route('/admin1025/orders', name: 'admin_orders')]
    public function index(OrderRepository $orderRepository): Response
    {
        return $this->render('Admin/order/orderList.html.twig', [
            'orders' => $orderRepository->findAll()
        ]);
    }

    #[Route('/admin1025/order/{id}', name: 'admin_order_show')]
    public function show(ManagerRegistry $managerRegistry, $id, OrderRepository $orderRepository, Request $request, OrderDetailsRepository $orderDetailsRepository): Response
    {
        $orderStateForm = $this->createForm(OrderStateType::class);
        $orderStateForm->handleRequest($request);
        $order = $orderRepository->find($id);
        $orderDetails = $orderDetailsRepository->find($id); 
        $manager = $managerRegistry->getManager();
        

        if ($orderStateForm->isSubmitted() && $orderStateForm->isValid()){

        $orderState = $orderStateForm->get('name')->getData();
           $order->setOrderState($orderState);
            dd($order);
            $manager->persist($order);

        }

        $manager->flush();

        return $this->render('Admin/order/orderShow.html.twig', [
            'order' => $order,
            'orderDetails' => $orderDetails,
            'orderStateForm' => $orderStateForm->createView(),
        ]);
    }
}
