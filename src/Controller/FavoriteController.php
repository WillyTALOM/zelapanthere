<?php

namespace App\Controller;

use App\Entity\Favorite;
use App\Entity\Product;
use App\Repository\FavoriteRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FavoriteController extends AbstractController
{
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry->getManager();
    }

    #[Route('/user/favorites', name: 'user_favorites')]
    public function index(): Response
    {
        return $this->render('favorite/index.html.twig');
    }

    #[Route('/admin/favorites', name: 'admin_favorites')]
    public function adminList(FavoriteRepository $favoriteRepository): Response
    {
        return $this->render('favorite/adminList.html.twig', [
            'favorites' => $favoriteRepository->findAll()
        ]);
    }

    #[Route('/favorite/add/{id}', name: 'favorite_add')]
    public function add(Product $product, Request $request): Response
    {
        if ($this->getUser()) {
            $userFavoritesProducts = [];
            foreach ($this->getUser()->getFavorites() as $userFavorite) {
                $userFavoritesProducts[] = $userFavorite->getProduct();
            }
            if (in_array($product, $userFavoritesProducts)) {
                $this->addFlash('danger', 'Vous avez déjà ajouté ' . $product->getName() . ' à vos favoris');
            } else {
                $favorite = new Favorite();
                $favorite->setUser($this->getUser());
                $favorite->setProduct($product);
                $this->managerRegistry->persist($favorite);
                $this->managerRegistry->flush();
                $this->addFlash('success', $product->getName() . ' a bien été ajouté à vos favoris');
            }
            return $this->redirect($request->headers->get('referer'));
        } else {
            $this->addFlash('danger', 'Vous devez posséder un compte TTG et être connecté pour ajouter des produits à vos favoris');
            return $this->redirectToRoute('home');
        }
    }

    #[Route('/favorite/delete/{id}', name: 'favorite_delete')]
    public function delete(Favorite $favorite, Request $request): Response
    {
        if ($favorite->getUser() === $this->getUser() || str_contains($request->headers->get('referer'), 'admin')) {
            $this->managerRegistry->remove($favorite);
            $this->managerRegistry->flush();
            if (str_contains($request->headers->get('referer'), 'admin')) {
                $this->addFlash('success', 'Le favori a bien été supprimé');
            } else {
                $this->addFlash('success', $favorite->getProduct()->getName() . ' a bien été supprimé de vos favoris');
            }
            return $this->redirect($request->headers->get('referer'));
        } else {
            $this->addFlash('danger', 'Vous n\'avez pas les droits pour supprimer ce favori');
            return $this->redirectToRoute('home');
        }
    }
}
