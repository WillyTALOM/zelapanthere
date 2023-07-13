<?php

namespace App\Controller\Admin;

use App\Entity\OrderDetails;
use App\Entity\OrderState;
use App\Form\OrderStateType;
use App\Repository\OrderRepository;
use App\Repository\OrderStateRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Repository\OrderDetailsRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    #[Route('/admin1025/order/{orderState}', name: 'admin_orders')]
    public function index(string $orderState, OrderRepository $orderRepository, OrderStateRepository $orderStateRepository,Request $request, PaginatorInterface $paginator): Response
    {
        $orderState = $orderStateRepository->findOneBy(['name' => $orderState]);
        $orders = $orderRepository->findBy(['orderState' => $orderState]);
        $data = $orders;
        $orders = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            5
        );
        return $this->render('Admin/order/orderList.html.twig', [
            'orders' => $orders
        ]);
        
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

    #[Route('/admin1025/order/{id}', name: 'admin_order_show')]
    public function show(ManagerRegistry $managerRegistry, $id, OrderRepository $orderRepository, Request $request, OrderDetailsRepository $orderDetailsRepository): Response
    {
        $orderStateForm = $this->createForm(OrderStateType::class);
        $orderStateForm->handleRequest($request);
        $order = $orderRepository->find($id);
        $orderDetails = $orderDetailsRepository->find($id); 
        $manager = $managerRegistry->getManager();
        $orderState = $orderStateForm->get('name')->getData();

        if ($orderStateForm->isSubmitted() && $orderStateForm->isValid()){

           $order->setOrderState($orderState);
            $manager->persist($order);
            $manager->flush();
        $this->addFlash('success', 'Le statut de la commande a bien été modifié');
            return $this->redirectToRoute('admin_orders', ['orderState'=>'payé'], Response::HTTP_SEE_OTHER);

        }

        

        return $this->render('Admin/order/orderShow.html.twig', [
            'order' => $order,
            'orderDetails' => $orderDetails,
            'orderStateForm' => $orderStateForm->createView(),
        ]);
    }
}
