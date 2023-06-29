<?php

namespace App\Controller;

use App\Entity\Order;
use App\Repository\OrderStateRepository;
use Stripe\StripeClient;
use App\Service\CartService;
use Symfony\Component\Mime\Address;
use Doctrine\Persistence\ManagerRegistry;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PaymentController extends AbstractController
{
   #[Route('payment/cancel', name: 'payment_cancel')]
    public function cancel(Request $request): Response
    {
        if ($request->headers->get('referer') !== 'https://checkout.stripe.com/') { // vérifie qu'on vient bien de Stripe
            return $this->redirectToRoute('cart');
        }

        return $this->render('payment/cancel.html.twig');
    }
    
    #[Route('payment-stripe/{order}', name: 'payment_stripe')]
    public function stripe(Request $request, CartService $cartService, Order $order): Response
    {
        // si on ne vient pas de la page de validation du panier, on redirige
        // if ($request->headers->get('referer') !== $this->getParameter('domain') . '/cart/validation') {
        //     return $this->redirectToRoute('cart');
        // }


            $sessionCart = $cartService->getCart(); // récupère le panier en session
            $stripeCart = []; // initialise le panier Stripe (qui sera envoyé à Stripe)
            
            foreach ($sessionCart as $line) { // transforme le panier session en panier Stripe
                if ($line['product']->getReduction() > 0) {
                    $stripeElement = [ // produit tel que Stripe en a besoin pour le traiter, les noms des index sont importants
                        'quantity' => $line['quantity'],
                        'price_data' => [
                            'currency' => 'EUR',
                            'unit_amount' => $line['product']->getPriceSold() * 100,
                            'product_data' => [
                                'name' => $line['product']->getName(),
                                // 'description' => $line['product']->getDescription(),
                                // 'images' => [
                                //     $this->getParameter('domain') . '/public/img/product/' . $line['product']->getImg1()
                                // ]
                            ]
                        ]
                    ];
                $stripeCart[] = $stripeElement;
            } else {
                $stripeElement = [ // produit tel que Stripe en a besoin pour le traiter, les noms des index sont importants
                    'quantity' => $line['quantity'],
                    'price_data' => [
                        'currency' => 'EUR',
                        'unit_amount' => $line['product']->getPrice() * 100,
                        'product_data' => [
                            'name' => $line['product']->getName(),
                            // 'description' => $line['product']->getDescription(),
                            // 'images' => [// 'https://127.0.0.1:8000/public/img/product/' . $line['product']->getImg1()]
                        ]
                    ]
                ];
                $stripeCart[] = $stripeElement;
                }
            }
                
            $carrier = $order->getCarrier();
            $stripeElement = [
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'EUR',
                    'unit_amount' => $carrier->getPrice() * 100,
                    'product_data' => [
                        'name' => 'Livraison : ' . $carrier->getName(),
                        // 'images' => [
                        //     $this->getParameter('product_image_dir') . '/' . $carrier->getImg()
                        // ]
                    ]
                ]
            ];
            $stripeCart[] = $stripeElement;

            $stripe = new StripeClient($this->getParameter('stripe_secret_key')); // initialise Stripe avec la clé secrète

            $stripeSession = $stripe->checkout->sessions->create([ // crée la session de paiement Stripe
                'line_items' => $stripeCart,
                'mode' => 'payment',
                'success_url' => $this->getParameter('domain') . '/payment/' . $order->getId() . '/success',
                'cancel_url' => $this->getParameter('domain') . '/payment/cancel',
                'payment_method_types' => ['card']
            ]);

            return $this->render('payment/index.html.twig', [
            'sessionId' => $stripeSession->id,
            'order' => $order->getId()
        ]);
    }

     

    #[Route('payment/{order}/success', name: 'payment_success')]
    public function success(Request $request, CartService $cartService, Order $order, OrderStateRepository $orderStateRepository, ManagerRegistry $managerRegistry, MailerInterface $mailer): Response
    {
        if ($request->headers->get('referer') !== 'https://checkout.stripe.com/') { // vérifie qu'on vient bien de Stripe
            return $this->redirectToRoute('cart');
        }
        $cartService->clear(); // vide le panier
        
        $order->setOrderState($orderStateRepository->findOneBy(['name' => 'payé'])); // définit le statut de la commande à "payé"
        $managerRegistry->getManager()->persist($order);
        $managerRegistry->getManager();
        
        $email = (new TemplatedEmail()) // email pour informer l'admin de la nouvelle commande à expédier
            ->from(new Address($this->container->get('twig')->getGlobals()['contact_email'], 'Mamayayatoh'))
            ->to(new Address($this->container->get('twig')->getGlobals()['contact_email']))
            ->replyTo(new Address($this->container->get('twig')->getGlobals()['contact_email'], 'Mamayayatoh'))
            ->subject('Nouvelle commande')
            ->htmlTemplate('email/order_new.html.twig')
            ->context([
                'order' => $order,
                'orderDetails' => $order->getOrderDetails()
            ]);
        $mailer->send($email);

        $email = (new TemplatedEmail()) // email récapitulatif pour le client
            ->from(new Address($this->container->get('twig')->getGlobals()['contact_email'], 'Mamayayatoh'))
            ->to(new Address($order->getUser()->getEmail(), $order->getUser()->getFirstName() . strtoupper($order->getUser()->getLastName())))
            ->replyTo(new Address($this->container->get('twig')->getGlobals()['contact_email'], 'Mamayayatoh'))
            ->subject('Récapitulatif de commande')
            ->htmlTemplate('email/order_confirmation.html.twig')
            ->context([
                'order' => $order,
                'orderDetails' => $order->getOrderDetails()
            ]);
        $mailer->send($email);

        foreach ($order->getOrderDetails() as $orderDetail) { // gestion des stocks restants en base de données
            $product = $orderDetail->getProductId();
            $product->setQuantity($product->getQuantity() - $orderDetail->getQuantity());
            $managerRegistry->getManager()->persist($product);
        }

        $managerRegistry->getManager()->flush();

        return $this->render('payment/success.html.twig');
    }

    
}