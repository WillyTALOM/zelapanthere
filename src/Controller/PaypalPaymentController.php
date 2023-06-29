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
    #[Route('/payment-paypal', name: 'payment_paypal')]
    public function ui(CartService $cart): string
    {
     
        
        $clientSecret = 'EEUfWThCSBIzQigDSHKjL31TjK5a6myRrfLm0eq1PYFJ2q4ACyjBVZs1nfg0yS0W8YBBO6gWJTufo-fJ';
         
        
        $clientId = 'Afiy80K595O5adN8b0etICGx0UZz0Bw1uGwMmPJ55dXovRaAAbEMO9SumubGfWR3lnQp-6nSoKoW1fks';
        $order = json_encode([
            'purchase_units' => [
                [
                    'description' => 'Panier',
                    'items'       => array_map(function ($product) {
                        return [
                            'name'        => $line['product']->getName(),
                            'quantity'    => 1,
                            'unit_amount' => [
                                'value'         => number_format($product['price'] / 100, 2, '.', ""), // Mes sommes sont en centimes d'euros
                                'currency_code' => 'EUR',
                            ]
                        ];
                    }, $cart->getCart()),
                    'amount'      => [
                        'currency_code' => 'EUR',
                        'value'         => number_format($cart->getTotal() / 100, 2, '.', ""),
                        'breakdown'     => [
                            'item_total' => [
                                'currency_code' => 'EUR',
                                'value'         => number_format($cart->getTotal() / 100, 2, '.', "")
                            ]
                        ]
                    ]
                ]
            ]
        ]);
        return <<<HTML
        <script src="https://www.paypal.com/sdk/js?client-id={$clientId}&currency=EUR&intent=authorize"></script>
        <div id="paypal-button-container"></div>
        <script>
          paypal.Buttons({
            // Sets up the transaction when a payment button is clicked
            createOrder: (data, actions) => {
              return actions.order.create({$order});
            },
            // Finalize the transaction after payer approval
            onApprove: async (data, actions) => {
              const authorization = await actions.order.authorize()
              const authorizationId = authorization.purchase_units[0].payments.authorizations[0].id
              await fetch('/paypal.php', {
                method: 'post',
                headers: {
                  'content-type': 'application/json'
                },
                body: JSON.stringify({authorizationId})
              })
              alert('Votre paiement a bien été enregistré')
            }
          }).render('#paypal-button-container');
        </script>
    HTML;
    }
    // public function paypal(Request $request, CartService $cartService, Order $order,ManagerRegistry $managerRegistry, UrlGeneratorInterface $urlGeneratorInterface): RedirectResponse{
        
    //     $manager = $managerRegistry->getManager();
    //     $sessionCart = $cartService->getCart(); // récupère le panier en session
    //     $items = []; // initialise le panier paypal (qui sera envoyé à paypal)
    //     $itemTotal = 0;
    //     foreach ($sessionCart as $line) { // transforme le panier session en panier paypal
    //         if ($line['product']->getReduction() > 0) {
    //             $items = [ // produit tel que Stripe en a besoin pour le traiter, les noms des index sont importants
    //                 'name' => $line['product']->getName(),
    //                 'quantity' => $line['quantity'],
    //                 'unit_amount' => [
    //                     'currency_code' => 'EUR',
    //                     'value' => $line['product']->getPriceSold() * 100,
    //                 ]
    //             ];
    //         $itemTotal += $line['quantity'] * $line['product']->getPriceSold();
            
    //     } 
    //     $carrier = $order->getCarrier();
    //     $total = $itemTotal + $carrier->getPrice();

    //     $request = new OrdersCreateRequest();
    //     // $request->prefer('return-representation');

    //     $request->body = [
    //         'intent' => 'CAPTURE',
    //         'purchase_units' => [
    //             [
    //                 'amount' => [
    //                     'currency_code' => 'EUR',
    //                     'value' => $total,
    //                     'breakdown' => [
    //                         'item_total' => [
    //                             'currency_code' => 'EUR',
    //                             'value' => $itemTotal
    //                         ],
    //                         'shipping' => [
    //                             'currency_code' => 'EUR',
    //                             'value' => $carrier->getPrice()
    //                         ]
    //                     ]
    //                 ],
    //                 'items' => $items
    //             ]
    //             ],
    //             'application_context' => [
    //                 'return_url' => $urlGeneratorInterface->generate(
    //                     'payment_success_paypal',
    //                     ['order' => $order->getId()],
    //                     UrlGeneratorInterface::ABSOLUTE_URL 
    //                 ),
    //                 'cancel_url' => $urlGeneratorInterface->generate(
    //                     'payment_cancel_paypal',
    //                     ['order' => $order->getId()], 
    //                     UrlGeneratorInterface::ABSOLUTE_URL 
    //                 )
    //             ]
    //         ];

    //     }
    //     $client = $this->getClientPaypal();
    //     $response = $client->execute($request);
    //     if($response->statusCode !== 201){
    //         return $this->redirectToRoute('Cart');
    //     }

    //     $approvalLink = '';
    //     foreach($response->result->links as $link){
    //         if($link->rel === 'approve'){
    //            $approvalLink = $link->href;
    //            break;
    //         }
    //     }
    //     if(empty($approvalLink)){
    //         return $this->redirectToRoute('Cart');  
    //     }
    //     $order->setPaypal($response->result->id);

    //     $manager->persist($order);
    //     $manager->flush();

    //     return new RedirectResponse($approvalLink);
    // }
    
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
