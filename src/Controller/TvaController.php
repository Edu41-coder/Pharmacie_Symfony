<?php

namespace App\Controller;

use App\Entity\Tva;
use App\Form\TvaType;
use App\Service\TvaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TvaController extends AbstractController
{
    private $tvaService;
    private $entityManager;

    public function __construct(TvaService $tvaService, EntityManagerInterface $entityManager)
    {
        $this->tvaService = $tvaService;
        $this->entityManager = $entityManager;
    }

    #[Route('/tva', name: 'tva_show', methods: ['GET'])]
    public function show(): Response
    {
        $tva = $this->tvaService->getCurrentTva();

        return $this->render('tva/show.html.twig', [
            'tva' => $tva,
        ]);
    }

    #[Route('/tva/edit', name: 'tva_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request): Response
    {
        $tva = $this->tvaService->getCurrentTva();
        $form = $this->createForm(TvaType::class, $tva);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $tva->setUpdatedAt(new \DateTime());
            $this->entityManager->flush();

            $this->addFlash('success', 'Le taux de TVA a été mis à jour avec succès.');
            return $this->redirectToRoute('tva_show');
        }

        return $this->render('tva/edit.html.twig', [
            'tva' => $tva,
            'form' => $form->createView(),
        ]);
    }
}
