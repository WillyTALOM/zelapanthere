<?php

namespace App\Controller\Admin;

use App\Entity\Carrier;
use App\Form\CarrierType;
use App\Repository\CarrierRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CarrierController extends AbstractController
{
    #[Route('/admin1025/carriers', name: 'admin_carriers')]
    public function index(CarrierRepository $carrierRepository): Response
    {
        return $this->render('Admin/carrier/adminList.html.twig', [
            'carriers' => $carrierRepository->findAll()
        ]);
    }

    #[Route('/admin1025/carrier/create', name: 'carrier_create')]
    public function create(Request $request, ManagerRegistry $managerRegistry): Response
    {
        $carrier = new Carrier();
        $carrierForm = $this->createForm(CarrierType::class, $carrier);
        $carrierForm->handleRequest($request);

        if ($carrierForm->isSubmitted() && $carrierForm->isValid()) {

            $image = $carrierForm['image']->getData();
            $imageName = time() . '-1.' . $image->guessExtension();
            $image->move($this->getParameter('carrier_image_dir'), $imageName);
            $carrier->setImage($imageName);

            $managerRegistry->getManager()->persist($carrier);
            $managerRegistry->getManager()->flush();

            $this->addFlash('success', 'Le transporteur a bien été créé');
            return $this->redirectToRoute('admin_carriers');
        }

        return $this->render('Admin/carrier/form.html.twig', [
            'carrierForm' => $carrierForm->createView()
        ]);
    }

    #[Route('/admin/carrier/update/{carrier}', name: 'carrier_update')]
    public function update(Carrier $carrier, Request $request, ManagerRegistry $managerRegistry): Response
    {
        $carrierForm = $this->createForm(CarrierType::class, $carrier);
        $carrierForm->handleRequest($request);

        if ($carrierForm->isSubmitted() && $carrierForm->isValid()) {

            $image = $carrierForm['image']->getData();
            if ($image !== null) {
                $oldImagePath = $this->getParameter('carrier_image_dir') . '/' . $carrier->getImage();
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
                $newImageName = time() . '-1.' . $image->guessExtension();
                $image->move($this->getParameter('carrier_image_dir'), $newImageName);
                $carrier->setImage($newImageName);
            }

            $managerRegistry->getManager()->persist($carrier);
            $managerRegistry->getManager()->flush();

            $this->addFlash('success', 'Le transporteur a bien été créé');
            return $this->redirectToRoute('admin_carriers');
        }

        return $this->render('Admin/carrier/form.html.twig', [
            'carrierForm' => $carrierForm->createView()
        ]);
    }

    #[Route('/admin1025/carrier/delete/{carrier}', name: 'carrier_delete')]
    public function delete(Carrier $carrier, ManagerRegistry $managerRegistry): Response
    {
        $imagePath = $this->getParameter('carrier_image_dir') . '/' . $carrier->getImage();
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
        $managerRegistry->getManager()->remove($carrier);
        $managerRegistry->getManager()->flush();
        $this->addFlash('success', 'Le transporteur a bien été supprimé');
        return $this->redirectToRoute('admin_carriers');
    }
}
