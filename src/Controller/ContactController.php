<?php

namespace App\Controller;

use App\Form\ContactType;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ContactController extends AbstractController
{
    #[Route('/contact', name: 'contact')]
    public function index(Request $request, SluggerInterface $slugger, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactType::class);
        $form->handleRequest($request);
        $contact_logo = $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/img/category/1688415669-1.png');

        if($form->isSubmitted() && $form->isValid()){
         $contact = ($form->getData());
         $email = (new TemplatedEmail())
              ->from(new Address($contact["email"], $contact["firstName"] . '' . $contact["lastName"])) 
              ->to(new Address($this->getParameter('contact_email')))
              ->replyTo(new Address($contact['email'], $contact['firstName']. '' . $contact['lastName']))
              ->subject('Demande de contact - ' . $contact['subject'])
              ->htmlTemplate('contact/email_contact.html.twig')
              ->context([
                'firstName' => htmlspecialchars($contact['firstName']),
                'lastName' => htmlspecialchars($contact['lastName']),
                'company' => htmlspecialchars($contact['company']),
                'phone' => htmlspecialchars($contact['phone']),
                'emailAddress' => htmlspecialchars($contact['email']),
                'subject' => htmlspecialchars($contact['subject']),
                'message' => htmlspecialchars($contact['message']),
                'contact_logo' => $contact_logo           
              ]); 
              
              if ($contact['attachment'] !== null) {
                $originalFileName = pathinfo($contact['attachment']->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFileName = $slugger->slug($originalFileName);
                $newFileName = $safeFileName . '.' . $contact['attachment']->guessExtension();
                $email->attachFromPath($contact['attachment']->getPathName(), $newFileName);
            }

            $mailer->send($email);
            $this->addFlash('success', 'Votre message a bien été envoyé. Merci de nous avoir contacté. Notre équipe va vous répondre dans les meilleurs délais.');
            return $this->redirectToRoute('contact');
            
        }

        return $this->render('contact/index.html.twig', [
            'contactForm' => $form->createView(),
        ]);
    }
 
    private function imageToBase64($path) {
        $path = $this->getParameter('kernel.project_dir') . '/public/img/category/1688415669-1.png';
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }
}
