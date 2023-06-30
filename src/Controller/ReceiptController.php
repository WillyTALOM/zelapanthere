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


        $html = $this->renderView('receipt/orderReceipt.html.twig', [
            'product' => $product,
            'order' => $order,
            'user' => $this->getUser(),
            'logo'  => $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/img/profile.png'),
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
            // 'orderDetails' => $orderDetails

        ]);
    }
    
          $data = [
            'imageSrc'  => $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/img/profile.png'),
            'name'         => 'John Doe',
            'address'      => 'USA',
            'mobileNumber' => '000000000',
            'email'        => 'john.doe@email.com'
        ];
        $html =  $this->renderView('pdf_generator/index.html.twig', $data);
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->render();
         
        return new Response (
            $dompdf->stream('resume', ["Attachment" => false]),
            Response::HTTP_OK,
            ['Content-Type' => 'application/pdf']
        );
    }
 
    private function imageToBase64($path) {
        $path = $path;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }
}