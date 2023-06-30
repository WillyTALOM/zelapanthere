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
}