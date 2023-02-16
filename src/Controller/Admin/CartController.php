<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Entity\OrderDetails;
use App\Service\CartService;
use App\Form\CartValidationType;
use App\Repository\ProductRepository;
use App\Repository\FavoriteRepository;
use App\Repository\OrderStateRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
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
        // if ($request->headers->get('referer') === $this->getParameter('domain') . '/cart/') {
        //     return $this->redirectToRoute('cart');
        // }
        return $this->redirectToRoute('cart');
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
    public function validate(Request $request, OrderStateRepository $orderStateRepository, CartService $cartService, ManagerRegistry $managerRegistry): Response
    {
        $cartValidationForm = $this->createForm(CartValidationType::class);
        $cartValidationForm->handleRequest($request);

        if ($cartValidationForm->isSubmitted() && $cartValidationForm->isValid()) {

            $manager = $managerRegistry->getManager();
            $carrier = $cartValidationForm['carrier']->getData();
            
            $order = new Order(); // génère la commande en base de données
            $order->setReference('O' . date_format(new \DateTime(), 'Ymdhis'));
            $order->setAmount($cartService->getTotal() + $carrier->getPrice());
            $order->setCreatedAt(new \DateTimeImmutable());
            $order->setOrderState($orderStateRepository->findOneBy(['status' => 'attente paiement']));
            $order->setUser($this->getUser());
            $order->setBillingAddress($cartValidationForm['billing_address']->getData());
            $order->setDeliveryAddress($cartValidationForm['delivery_address']->getData());
            $order->setCarrier($carrier);

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

            return $this->redirectToRoute('payment', [
                'order' => $order->getId()
            ]);
        }

        return $this->render('cart/validation.html.twig', [
            'cart' => $cartService->getCart(),
            'total' => $cartService->getTotal(),
            'cartValidationForm' => $cartValidationForm->createView()
        ]);
    }

    public function getNbProducts(CartService $cartService): Response
    {
        return $this->render('cart/nbProducts.html.twig', [
            'nbProducts' => $cartService->getNbProducts()
        ]);
    }
}