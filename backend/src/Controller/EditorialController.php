<?php

namespace App\Controller;

use App\Entity\Editorial;
use App\Form\EditorialType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/editorial')]
final class EditorialController extends AbstractController
{
    #[Route(name: 'app_editorial_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $editorials = $entityManager
            ->getRepository(Editorial::class)
            ->findAll();

        return $this->render('editorial/index.html.twig', [
            'editorials' => $editorials,
        ]);
    }

    #[Route('/new', name: 'app_editorial_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $editorial = new Editorial();
        $form = $this->createForm(EditorialType::class, $editorial);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($editorial);
            $entityManager->flush();

            return $this->redirectToRoute('app_editorial_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('editorial/new.html.twig', [
            'editorial' => $editorial,
            'form' => $form,
        ]);
    }

    #[Route('/{editorial_id}', name: 'app_editorial_show', methods: ['GET'])]
    public function show(Editorial $editorial): Response
    {
        return $this->render('editorial/show.html.twig', [
            'editorial' => $editorial,
        ]);
    }

    #[Route('/{editorial_id}/edit', name: 'app_editorial_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Editorial $editorial, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EditorialType::class, $editorial);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_editorial_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('editorial/edit.html.twig', [
            'editorial' => $editorial,
            'form' => $form,
        ]);
    }

    #[Route('/{editorial_id}', name: 'app_editorial_delete', methods: ['POST'])]
    public function delete(Request $request, Editorial $editorial, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$editorial->getEditorial_id(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($editorial);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_editorial_index', [], Response::HTTP_SEE_OTHER);
    }
}
