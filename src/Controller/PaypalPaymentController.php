<?php

namespace App\Controller;

use App\Entity\Order;
use App\Service\CartService;
use App\Repository\OrderStateRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class PaypalPaymentController extends AbstractController
{
    #[Route('/payment-paypal/{order}', name: 'payment_paypal')]
    public function paypal(Request $request, CartService $cartService, Order $order,ManagerRegistry $managerRegistry, UrlGeneratorInterface $urlGeneratorInterface): RedirectResponse{
        
        $manager = $managerRegistry->getManager();
        $sessionCart = $cartService->getCart(); // récupère le panier en session
        $items = []; // initialise le panier paypal (qui sera envoyé à paypal)
        $itemTotal = 0;
        foreach ($sessionCart as $line) { // transforme le panier session en panier paypal
            if ($line['product']->getReduction() > 0) {
                $items = [ // produit tel que Stripe en a besoin pour le traiter, les noms des index sont importants
                    'name' => $line['product']->getName(),
                    'quantity' => $line['quantity'],
                    'unit_amount' => [
                        'currency_code' => 'EUR',
                        'value' => $line['product']->getPriceSold() * 100,
                    ]
                ];
            $itemTotal += $line['quantity'] * $line['product']->getPriceSold();
            
        } 
        $carrier = $order->getCarrier();
        $total = $itemTotal + $carrier->getPrice();

        $request = new OrdersCreateRequest();
        // $request->prefer('return-representation');

        $request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'amount' => [
                        'currency_code' => 'EUR',
                        'value' => $total,
                        'breakdown' => [
                            'item_total' => [
                                'currency_code' => 'EUR',
                                'value' => $itemTotal
                            ],
                            'shipping' => [
                                'currency_code' => 'EUR',
                                'value' => $carrier->getPrice()
                            ]
                        ]
                    ],
                    'items' => $items
                ]
                ],
                'application_context' => [
                    'return_url' => $urlGeneratorInterface->generate(
                        'payment_success_paypal',
                        ['order' => $order->getId()],
                        UrlGeneratorInterface::ABSOLUTE_URL 
                    ),
                    'cancel_url' => $urlGeneratorInterface->generate(
                        'payment_cancel_paypal',
                        ['order' => $order->getId()], 
                        UrlGeneratorInterface::ABSOLUTE_URL 
                    )
                ]
            ];

        }
        $client = $this->getClientPaypal();
        $response = $client->execute($request);
        if($response->statusCode !== 201){
            return $this->redirectToRoute('Cart');
        }

        $approvalLink = '';
        foreach($response->result->links as $link){
            if($link->rel === 'approve'){
               $approvalLink = $link->href;
               break;
            }
        }
        if(empty($approvalLink)){
            return $this->redirectToRoute('Cart');  
        }
        $order->setPaypal($response->result->id);

        $manager->persist($order);
        $manager->flush();

        return new RedirectResponse($approvalLink);
    }
    
    #[Route('/payment/{order}/success-paypal', name: 'payment_success_paypal')]
    public function successPaypal(Order $order, CartService $cartService, OrderStateRepository $orderStateRepository)
    {
           $order->getId();
           $cartService->clear(); // vide le panier
        
           $order->setOrderState($orderStateRepository->findOneBy(['name' => 'payé'])); 
           
           return $this->render('payment/success.html.twig');
    }


    #[Route('/payment/{order}/cancel-paypal', name: 'payment_cancel_paypal')]
    public function cancelPaypal()
    {
            
    }
}
