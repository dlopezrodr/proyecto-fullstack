<?php

namespace App\Controller;

use App\Entity\Libro;
use App\Entity\Autor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/api/libros')] 
final class LibroController extends AbstractController
{
    // ===============================================
    // R - READ (GET /api/libros)
    // ===============================================
    #[Route('', name: 'api_libros_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $libros = $entityManager
            ->getRepository(Libro::class)
            ->findAll();

        return $this->json($libros, Response::HTTP_OK);
    }

    // ===============================================
    // C - CREATE (POST /api/libros)
    // ===============================================
    #[Route('', name: 'api_libros_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json(['message' => 'Invalid JSON body.'], Response::HTTP_BAD_REQUEST);
        }

        $libro = new Libro();
        $libro->setTitulo($data['title'] ?? null); 
        $libro->setIsbn($data['isbn'] ?? null); 

        // Lógica para manejar la relación con Autor
        if (!empty($data['author'])) {
            $autor = $this->getOrCreateAutor($data['author'], $entityManager);
            $libro->setAutor($autor);
        }

        // Manejo de fecha desde Angular (publicationDate)
        if (!empty($data['publicationDate'])) {
            try {
                $libro->setFechaPublicacion(new \DateTime($data['publicationDate']));
            } catch (\Exception $e) {
                // Si la fecha tiene formato inválido, ignoramos o manejamos el error
            }
        }

        $entityManager->persist($libro);
        $entityManager->flush();

        return $this->json($libro, Response::HTTP_CREATED);
    }

    // ===============================================
    // R - READ (GET /api/libros/{id})
    // ===============================================
    #[Route('/{id}', name: 'api_libro_show', methods: ['GET'])]
    public function show(Libro $libro): Response
    {
        return $this->json($libro, Response::HTTP_OK);
    }

    // ===============================================
    // U - UPDATE (PUT /api/libros/{id})
    // ===============================================
    #[Route('/{id}', name: 'api_libro_edit', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, Libro $libro, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json(['message' => 'Invalid JSON body.'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['title'])) {
            $libro->setTitulo($data['title']);
        }

        if (isset($data['isbn'])) {
            $libro->setIsbn($data['isbn']);
        }

        // Actualización del autor si se envía en el JSON
        if (isset($data['author'])) {
            $autor = $this->getOrCreateAutor($data['author'], $entityManager);
            $libro->setAutor($autor);
        }

        if (isset($data['publicationDate'])) {
            $libro->setFechaPublicacion(new \DateTime($data['publicationDate']));
        }

        $entityManager->flush();

        return $this->json($libro, Response::HTTP_OK);
    }

    // ===============================================
    // D - DELETE (DELETE /api/libros/{id})
    // ===============================================
    #[Route('/{id}', name: 'api_libro_delete', methods: ['DELETE'])]
    public function delete(Libro $libro, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($libro);
        $entityManager->flush();

        return new Response(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Helper privado para buscar o crear un autor por nombre
     */
    private function getOrCreateAutor(string $nombre, EntityManagerInterface $em): Autor
    {
        $autor = $em->getRepository(Autor::class)->findOneBy(['nombre' => $nombre]);

        if (!$autor) {
            $autor = new Autor();
            $autor->setNombre($nombre);
            $autor->setApellido('.');
            $em->persist($autor);
            // No hacemos flush aquí, dejamos que el flush del controlador lo guarde todo
        }

        return $autor;
    }
}