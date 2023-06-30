<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Order;
use DateTimeImmutable;
use App\Entity\Address;
use App\Entity\OrderDetails;
use App\Service\CartService;
use App\Form\CartValidationType;
use App\Repository\UserRepository;
use App\Form\CartValidationInfoType;
use App\Repository\ProductRepository;
use App\Repository\FavoriteRepository;
use App\Repository\OrderStateRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class CartController extends AbstractController
{
    #[Route('/cart', name: 'cart')]
    public function index(CartService $cartService, FavoriteRepository $favoriteRepository, ProductRepository $productRepository): Response
    {
        $featuredProducts = [];
        if ($this->getUser()) {
            $favorites = $favoriteRepository->findBy(['user' => $this->getUser()], ['id' => 'DESC'], 4);
            foreach ($favorites as $favorite) {
                $featuredProducts[] = $favorite->getProduct();
            }
        }
        if (count($featuredProducts) < 4) {
            $dbRandomProducts = $productRepository->findRandom(4 - count($featuredProducts));
            foreach ($dbRandomProducts as $produit) {
                $featuredProducts[] = $productRepository->find($produit['id']);
            }
        }

        return $this->render('cart/index.html.twig', [
            'cart' => $cartService->getCart(),
            'total' => $cartService->getTotal(),
            'featuredProducts' => $featuredProducts
        ]);
    }

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(CartService $cartService, int $id, Request $request): Response
    {
        if ($cartService->add($id) === false) {
            $this->addFlash('danger', 'Pas possible, vous avez déjà vidé notre stock');
        } else {
            $this->addFlash('success', 'L\'article a bien été ajouté au panier');
        }
        if ($request->headers->get('referer') === $this->getParameter('domain') . '/cart/') {
            return $this->redirectToRoute('cart');
        }
        return $this->redirect($request->headers->get('referer'));
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove(CartService $cartService, int $id): Response
    {
        $cartService->remove($id);
        $this->addFlash('success', 'L\'article a bien été supprimé du panier');
        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/delete/{id}', name: 'cart_delete')]
    public function delete(CartService $cartService, int $id): Response
    {
        $cartService->delete($id);
        $this->addFlash('success', 'L\'article a bien été supprimé du panier');
        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/clear', name: 'cart_clear')]
    public function clear(CartService $cartService): Response
    {
        $cartService->clear();
        $this->addFlash('success', 'Votre panier a bien été vidé');
        return $this->redirectToRoute('cart');
    }

    #[Route('/cart/validation', name: 'cart_validation')]
    public function validate(UserRepository $userRepository, MailerInterface $mailer,SluggerInterface $slugger, Request $request, OrderStateRepository $orderStateRepository, CartService $cartService, ManagerRegistry $managerRegistry): Response
    {
        
        $manager = $managerRegistry->getManager();
        if($this->getUser()){
            $cartValidationForm = $this->createForm(CartValidationType::class);
            $cartValidationForm->handleRequest($request);
            
            
            if ($cartValidationForm->isSubmitted() && $cartValidationForm->isValid()) {
                $carrier = $cartValidationForm['carrier']->getData();
                $firstName = $cartValidationForm['firstName']->getData();
                $lastName = $cartValidationForm['lastName']->getData();
                
                $order = new Order(); // génère la commande en base de données
                $order->setReference('O' . date_format(new \DateTime(), 'Ymdhis'));
                $order->setAmount($cartService->getTotal() + $carrier->getPrice());
                $order->setCreatedAt(new \DateTimeImmutable());
                $order->setOrderState($orderStateRepository->findOneBy(['name' => 'attente paiement']));
                $order->setUser($this->getUser());
                $order->setBillingAddress($cartValidationForm['billing_address']->getData());
                $order->setDeliveryAddress($cartValidationForm['delivery_address']->getData());
                $order->setCarrier($carrier);
                $order->setMethod($cartValidationForm['payment']->getData());                
             
                $manager->persist($order);
                
                foreach ($cartService->getCart() as $line) {
                    $orderDetail = new OrderDetails();
                    $orderDetail
                        ->setOrderId($order)
                        ->setProductId($line['product'])
                        ->setQuantity($line['quantity']);
                    $manager->persist($orderDetail);
                }

                $manager->flush();

                // traite le transporteur comme un produit (± ajout au panier)
                // if($request->request->get('stripe')){
                if ($cartValidationForm['payment']->getData() === 'stripe'){
                    return $this->redirectToRoute('payment_stripe', [
                        'order' => $order->getId()
                    ]);}
                    else{return $this->redirectToRoute('payment_paypal', [
                        'order' => $order->getId()
                    ]);}
                        
                // }
                // if($request->request->get('paypal')){
                //     return $this->redirectToRoute('paymentpaypal', [
                //         'order' => $order->getId()
                //     ]);
                // }
            }

            return $this->render('cart/validation.html.twig', [
                'cart' => $cartService->getCart(),
                'total' => $cartService->getTotal(),
                'cartValidationForm' => $cartValidationForm->createView()
            ]);
        }else{
            $cartValidationInfoForm = $this->createForm(CartValidationInfoType::class);          

            $cartValidationInfoForm->handleRequest($request);  

            $carrier = $cartValidationInfoForm['carrier']->getData();
            
            $email = $cartValidationInfoForm['email']->getData();


            if ($cartValidationInfoForm->isSubmitted() && $cartValidationInfoForm->isValid()) {
                
                $users = $userRepository->findAll();

                $userEmails = [];

                foreach ($users as $newUser ) {
                    $userEmails[] = $newUser->getEmail();
                    }

                    if(in_array($email, $userEmails)){

                        $this->addFlash('danger', 'Vous possez déja un compte, merci de vous connecter pour passer votre commande.');
                        return $this->redirectToRoute('login');

                    }else{

                    $address = new Address();
                    $address->setAddress($cartValidationInfoForm['address']->getData());
                    $address->setAdditional($cartValidationInfoForm['additional']->getData());
                    $address->setCity($cartValidationInfoForm['city']->getData());
                    $address->setCountry($cartValidationInfoForm['country']->getData());
                    $address->setZip($cartValidationInfoForm['zip']->getData());

                    $manager->persist($address);


                    $user = new User();
                    $user->setEmail($cartValidationInfoForm['email']->getData() . 1);
                    $user->setLastName($cartValidationInfoForm['lastName']->getData());
                    $user->setFirstName($cartValidationInfoForm['firstName']->getData());
                    $user->setPhone($cartValidationInfoForm['phone']->getData());
                    $user->setRoles(["ROLE_USER"]);
                    $user->addAddress($address);
                    $user->setCreatedAt(new DateTimeImmutable());
                    $user->setIsVerified(1);
                    $user->setPassword($slugger->slug($cartValidationInfoForm['lastName']->getData() . 12345678));

                    $manager->persist($user);                  
                    
                    $order = new Order(); // génère la commande en base de données
                    $order->setReference('O' . date_format(new \DateTime(), 'Ymdhis'));
                    $order->setAmount($cartService->getTotal() + $carrier->getPrice());
                    $order->setCreatedAt(new \DateTimeImmutable());
                    $order->setOrderState($orderStateRepository->findOneBy(['name' => 'attente paiement']));
                    $order->setUser($user);
                    $order->setBillingAddress($address);
                    $order->setDeliveryAddress($address);
                    $order->setCarrier($carrier);
                    $order->setMethod('stripe');

                    $manager->persist($order);

                    
                    foreach ($cartService->getCart() as $line) {
                        $orderDetail = new OrderDetails();
                        $orderDetail
                            ->setOrderId($order)
                            ->setProductId($line['product'])
                            ->setQuantity($line['quantity']);
                        $manager->persist($orderDetail);
                    }
                   
               } 


                $manager->flush();

                // traite le transporteur comme un produit (± ajout au panier)
                
                return $this->redirectToRoute('payment_stripe', [
                    'order' => $order->getId()
                ]);
            }
        }

            return $this->render('cart/validationInfo.html.twig', [
                'cart' => $cartService->getCart(),
                'total' => $cartService->getTotal(),
                'cartValidationInfoForm' => $cartValidationInfoForm->createView()
            ]);


            
    }
    

    public function getNbProducts(CartService $cartService): Response
    {
        return $this->render('cart/nbProducts.html.twig', [
            'nbProducts' => $cartService->getNbProducts()
        ]);
    }
}