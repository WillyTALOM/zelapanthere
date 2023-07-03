<?php

namespace App\Controller;


use App\Form\ResetPasswordType;
use App\Form\ForgotPasswordType;
use App\Repository\UserRepository;
use Symfony\Component\Mime\Address;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class PasswordController extends AbstractController
{
    #[Route('/password', name: 'account_password')]
    public function index(Request $request, ManagerRegistry $managerRegistry, UserPasswordHasherInterface $userPasswordHasherInterface, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        $manager = $managerRegistry->getManager();
        $user = $this->getUser();
         $contact_logo = $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/img/category/1688415669-1.png');
        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPassword($userPasswordHasherInterface->hashPassword(
                $user,
                $form['plainPassword']->getData()
            ));
            // $user->setPassword($userPasswordHasherInterface->hashPassword(
            //     $user,
            //     $form['plainPassword']->getData()
            // ));

            $manager->persist($user);
            $manager->flush();
            $email = (new TemplatedEmail())
                ->from(new Address($this->getParameter('contact_email'), 'Ze'))
                ->to(new Address($user->getEmail(), $user->getfirstName(), $user->getlastName()))
                ->subject('VDV - Confirmation de modification de mot de passe')
                ->htmltemplate('password/password_change.html.twig')
                ->context([
                    'contact_logo' => $contact_logo,
                    'user' => $user
                ]);
            $mailer->send($email);

            $this->addFlash('success', 'Votre mot de passe a été changé');
            return $this->redirectToRoute('user_account');
        }

        return $this->render('password/account_password.html.twig', [
            'createPasswordForm' => $form->createView(),
        ]);
    }
    
    private function imageToBase64($path) {
        $path = $this->getParameter('kernel.project_dir') . '/public/img/category/1688415669-1.png';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }
    
    #[Route('/mot-de-passe-oublié ', name: 'reset_password')]
    public function forgottenPassword(MailerInterface $mailer, ManagerRegistry $managerRegistry, Request $request, UserRepository $userRepository, TokenGeneratorInterface $tokenGeneratorInterface): Response
    {
        $form = $this->createForm(ForgotPasswordType::class);
        $form->handleRequest($request);
        $manager = $managerRegistry->getManager();

        if ($form->isSubmitted() && $form->isValid()) {
            // récupère l'utilisateur par son email
            $user = $userRepository->findOneByEmail($form['email']->getData());
            //verifie si il ya un user
            if ($user) {
                // on génère un token de réinitialisation
                $token = $tokenGeneratorInterface->generateToken();
                $user->setResetToken($token);
                $manager->persist($user);
                $manager->flush();

                // générer un lien de réinitialisation
                $url = $this->generateUrl(
                    'generate_password',
                    ['token' => $token],
                    UrlGeneratorInterface::ABSOLUTE_URL
                );
                //  on crée les données du mail
                // $context = compact('url', 'user');
                // // ou
                /*
                $context = [
                    'url => $url,
                    'user => $user
                ]
                 */


                // envoi du mail
                $email = (new TemplatedEmail())
                    ->from(new Address($this->getParameter('contact_email'), 'Ze'))
                    ->to($form['email']->getData())
                    ->subject('Ze - Réinitialisation mot de passe')
                    ->htmltemplate('password/password_forgot.html.twig')
                    ->context([

                        'url' => $url,
                        'user' => $user
                    ]);
                $mailer->send($email);
                $this->addFlash('success', 'Un email vous a bien été envoyé.');
                return $this->redirectToRoute('login');
            }
            // User est null
            $this->addFlash('success', 'Un email voua a été envoyé');
            return $this->redirectToRoute('login');
        }

        return $this->render('password/reset_password_request.html.twig', [
            'requestPasswordForm' => $form->createView(),
        ]);
    }


    #[Route('/réinitialisation/{token}', name: 'generate_password')]
    public function resetPassword(string $token, Request $request, UserRepository $userRepository, ManagerRegistry $managerRegistry, UserPasswordHasherInterface $userPasswordHasherInterface): Response
    {
        // on verfie si on ce token en bdd
        $user = $userRepository->findOneByResetToken($token);
        if ($user) {
            $form = $this->createForm(ResetPasswordType::class);
            $form->handleRequest($request);
            $manager = $managerRegistry->getManager();


            if ($form->isSubmitted() && $form->isValid()) {
                // on efface le token
                $user->setResetToken('0');
                $user->setPassword(
                    $userPasswordHasherInterface->hashPassword(
                        $user,
                        $form['plainPassword']->getData()
                    )
                );

                $manager->persist($user);
                $manager->flush();

                $this->addFlash('success', 'Votre mot de passe a été changé');
                return $this->redirectToRoute('login');
            }

            return $this->render(
                'password/create_password.html.twig',
                [
                    'createPasswordForm' => $form->createView()
                ]
            );
        }
        $this->addFlash('danger', 'Jeton invalid');
        return $this->redirectToRoute('login');
    }
}
