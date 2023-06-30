<?php

namespace App\Controller;

use App\Repository\OrderDetailsRepository;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ReceiptController extends AbstractController
{
    #[Route('/receipt/{reference}', name: 'receipt_pdf')]
    public function pdf(string $reference, OrderRepository $orderRepository, ProductRepository $productRepository): Response
    {
        $pdfOptions = new Options();

        $pdfOptions->set('defaultFont', 'Arial');

        $dompdf = new Dompdf();

        $order = $orderRepository->findOneByReference($reference);
        $product = $productRepository->findOneByReference($reference);
        $product = $productRepository->findAll();
        $company_company = $this->container->get('twig')->getGlobals()['contact_company']);
        $company_address = $this->container->get('twig')->getGlobals()['contact_address']);      
        $contact_zip = $this->container->get('twig')->getGlobals()['contact_zip']);
        $contact_city = $this->container->get('twig')->getGlobals()['contact_city']);
        $contact_phone = $this->container->get('twig')->getGlobals()['contact_phone']);
        $contact_email = $this->container->get('twig')->getGlobals()['contact_email']);
        $contact_siret = $this->container->get('twig')->getGlobals()['contact_siret']);
        $contact_country = $this->container->get('twig')->getGlobals()['contact_country']);
        $contact_logo = $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/img/panthere.jpeg'),

        $html = $this->renderView('receipt/orderReceipt.html.twig', [
            'product' => $product,
            'order' => $order,
            'user' => $this->getUser(),
            'contact_company' => $contact_company,
            'contact_address' => $contact_address,
            'contact_zip' => $contact_zip,
            'contact_city' => $contact_city,
            'contact_phone' => $contact_phone,
            'contact_email' => $contact_email,
            'contact_siret' => $contact_siret,
            'contact_country' => $contact_country,
            'contact_logo'  => $contact_logo,
            // 'orderDetails' => $orderDetails

        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream('facture', [
            "Attachment" => true
        ]);

        return $this->render('receipt/orderReceipt.html.twig', [
            'product' => $product,
            'order' => $order,
            'user' => $this->getUser(),
            'contact_company' => $contact_company,
            'contact_address' => $contact_address,
            'contact_zip' => $contact_zip,
            'contact_city' => $contact_city,
            'contact_phone' => $contact_phone,
            'contact_email' => $contact_email,
            'contact_siret' => $contact_siret,
            'contact_country' => $contact_country,
            'contact_logo'  => $contact_logo,
            // 'orderDetails' => $orderDetails

        ]);
 
    private function imageToBase64($path) {
        $path = $path;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }
}