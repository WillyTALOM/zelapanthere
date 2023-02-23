<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\UserType;
use DateTimeImmutable;
use App\Entity\Address;
use App\Repository\UserRepository;
use App\Repository\AddressRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    #[Route('/admin102/users', name: 'admin_users')]
    public function index(AddressRepository $addressRepository, UserRepository $userRepository): Response
    {
        $addresses = $addressRepository->findAll();
        $users =  $userRepository->findAll();
        return $this->render('Admin/user/userList.html.twig', [
            'users' => $users,
            'addresses' => $addresses
        ]);
    }


    #[Route('/admin1025/user/create', name: 'admin_user_create')]
    public function new(ManagerRegistry $managerRegistry, Request $request, UserPasswordHasherInterface $userPasswordHasher, UserRepository $userRepository, AddressRepository $addressRepository): Response
    {

        $user = new User();

        $address = new Address();


        $user->getAddresses()->add($address);



        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $users = $userRepository->findAll();
            $userFirstNames = [];
            $userLastNames = [];
            $userRoles = [];




            foreach ($users as $newUser) {
                $userFirstNames[] = $newUser->getFirstName();
                $userLastNames[] = $newUser->getLastName();
                $userRoles[] = $newUser->getRoles();
            }



            $addresses = $addressRepository->findAll();
            $newAddress = [];
            $addressAdditional = [];
            $addressZip = [];
            $addressCity = [];
            $addressCountry = [];

            foreach ($addresses as $existingAddress) {
                $newAddress[] = $existingAddress->getAddress();
                $addressAdditional[] = $existingAddress->getAdditional();
                $addressZip[] = $existingAddress->getZip();
                $addressCity[] = $existingAddress->getCity();
                $addressCountry[] = $existingAddress->getCity();
            }



            $user->setPassword($userPasswordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            ));


            $user->setCreatedAt(new DateTimeImmutable());

            $address->setUser($user);

            $manager = $managerRegistry->getManager();
            $manager->persist($user);
            $manager->persist($address);
            $manager->flush();

            $this->addFlash('success', 'L\'Utilisateur a bien été créé');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('Admin/user/form.html.twig', [
            'userForm' => $form->createView(),


        ]);
    }

    #[Route('/admin1025/user/{id}', name: 'admin_user_show')]
    public function show($id, AddressRepository $addressRepository, UserRepository $userRepository): Response
    {
        $address = $addressRepository->find($id);
        $user =  $userRepository->find($id);
        return $this->render('Admin/user/show.html.twig', [
            'user' => $user,
            'address' => $address
        ]);
    }



    #[Route('/admin1025/user/update/{id}', name: 'admin_user_update')]
    public function edit(UserPasswordHasherInterface $userPasswordHasher, Request $request, User $user, UserRepository $userRepository, ManagerRegistry $managerRegistry, AddressRepository $addressRepository): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $users = $userRepository->findAll();
            $userFirstNames = [];
            $userLastNames = [];
            $userRoles = [];
            $userPassword = [];
            foreach ($users as $newUser) {
                $userFirstNames[] = $newUser->getFirstName();
                $userLastNames[] = $newUser->getLastName();
                $userRoles[] = $newUser->getRoles();
                $userPassword[] = $newUser->getPassword();
            }

            $addresses = $addressRepository->findAll();
            $newAddress = [];
            $addressAdditional = [];
            $addressZip = [];
            $addressCity = [];
            $addressCountry = [];

            foreach ($addresses as $existingAddress) {
                $newAddress[] = $existingAddress->getAddress();
                $addressAdditional[] = $existingAddress->getAdditional();
                $addressZip[] = $existingAddress->getZip();
                $addressCity[] = $existingAddress->getCity();
                $addressCountry[] = $existingAddress->getCity();
            }
            $user->getPassword($userPasswordHasher->hashPassword(
                $user,
                $form->get('password')->getData()
            ));


            $manager = $managerRegistry->getManager();
            $manager->persist($user);
            $manager->flush();

            $this->addFlash('success', 'L\'Utilisateur a bien été modifié');
            return $this->redirectToRoute('admin_users');
        }

        return $this->render('Admin/user/form.html.twig', [
            'userForm' => $form->createView()
        ]);
    }

    #[Route('/admin1025/user/delete/{id}', name: 'admin_user_delete')]
    public function delete(User $user, ManagerRegistry $managerRegistry): Response
    {
        $manager = $managerRegistry->getManager();
        $manager->remove($user);
        $manager->flush();

        $this->addFlash('success', 'L\'Utilisateur a bein été supprimé');
        return $this->redirectToRoute('admin_users');
    }
}
