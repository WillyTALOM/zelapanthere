<?php

namespace App\Controller;

use App\Entity\User;
use DateTimeImmutable;
use App\Entity\Address;
use App\Security\EmailVerifier;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Repository\AddressRepository;
use App\Security\LoginFormAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    #[Route('/inscrivez-vous', name: 'register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, UserAuthenticatorInterface $userAuthenticator, LoginFormAuthenticator $authenticator, EntityManagerInterface $entityManager, UserRepository $userRepository, AddressRepository $addressRepository): Response
    {
        $user = new User();
        $address = new Address();


        $user->getAddresses()->add($address);
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $users = $userRepository->findAll();
            $userFirstNames = [];
            $userLastNames = [];
           
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

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

            $address->setUser($user);
            $user->setCreatedAt(new DateTimeImmutable());
            $user->setRoles(["ROLE_USER"]);
            $entityManager->persist($address);
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation(
                'verify_email', 
                $user,
                (new TemplatedEmail())
                    ->from($this->getParameter('contact_email'))
                    ->to($user->getEmail())
                    ->subject('Confirmez votre adresse email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
                    ->context([
                        'user' => $user,



                    ])
            );
            // do anything else you need here, like send an email

            $this->addFlash('success', 'Votre compte a bien été créé. Avant de continuer, vous devez valider votre adresse mail en cliquant sur le lien dans le mail que vous avez reçu.');
            return $this->redirectToRoute('login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify/email', name: 'verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Votre adresse email a bien été vérifiée.');
        
        return $this->redirectToRoute('home');
    }
}
