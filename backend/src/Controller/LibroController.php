<?php

namespace App\Controller;

use App\Entity\Libro;
use App\Entity\Autor;
use App\Entity\Editorial;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/libros')] 
final class LibroController extends AbstractController
{
    // ===============================================
    // R - READ (GET /api/libros)
    // ===============================================
    #[Route('', name: 'api_libros_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
{
    $libros = $entityManager->getRepository(Libro::class)->findAll();

    // Forzamos un array simple si el serializador automático falla
    $data = [];
    foreach ($libros as $libro) {
        $data[] = [
            'id' => $libro->getId(),
            'titulo' => $libro->getTitulo(),
            'isbn' => $libro->getIsbn(),
            'autor' => [
                'nombre' => $libro->getAutor()?->getNombre() ?? 'Sin autor'
            ],
            'editorial' => [
                'nombre' => $libro->getEditorial()?->getNombre() ?? 'N/A'
            ],
            'fechaPublicacion' => $libro->getFechaPublicacion()?->format('Y-m-d')
        ];
    }

    return $this->json($data, Response::HTTP_OK);
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

        // Validación de ISBN único para evitar errores 500
        if (!empty($data['isbn'])) {
            $existingBook = $entityManager->getRepository(Libro::class)->findOneBy(['isbn' => $data['isbn']]);
            if ($existingBook) {
                return $this->json(['message' => 'El ISBN ya existe.'], Response::HTTP_CONFLICT);
            }
        }

        $libro = new Libro();
        $libro->setTitulo($data['title'] ?? null); 
        $libro->setIsbn($data['isbn'] ?? null); 

        // Relación con Autor
        if (!empty($data['author'])) {
            $autor = $this->getOrCreateAutor($data['author'], $entityManager);
            $libro->setAutor($autor);
        }

        // Relación con Editorial
        if (!empty($data['editorial'])) {
            $editorial = $this->getOrCreateEditorial($data['editorial'], $entityManager);
            $libro->setEditorial($editorial);
        }

        // Manejo de fecha
        if (!empty($data['publicationDate'])) {
            try {
                $libro->setFechaPublicacion(new \DateTime($data['publicationDate']));
            } catch (\Exception $e) {
                // Formato de fecha inválido
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

        if (isset($data['title'])) $libro->setTitulo($data['title']);
        if (isset($data['isbn'])) $libro->setIsbn($data['isbn']);

        if (isset($data['author'])) {
            $autor = $this->getOrCreateAutor($data['author'], $entityManager);
            $libro->setAutor($autor);
        }

        if (isset($data['editorial'])) {
            $editorial = $this->getOrCreateEditorial($data['editorial'], $entityManager);
            $libro->setEditorial($editorial);
        }

        if (isset($data['publicationDate'])) {
            try {
                $libro->setFechaPublicacion(new \DateTime($data['publicationDate']));
            } catch (\Exception $e) { }
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

    // ===============================================
    // HELPERS PRIVADOS
    // ===============================================

    private function getOrCreateAutor(string $nombre, EntityManagerInterface $em): Autor
    {
        $autor = $em->getRepository(Autor::class)->findOneBy(['nombre' => $nombre]);
        if (!$autor) {
            $autor = new Autor();
            $autor->setNombre($nombre);
            $autor->setApellido('.'); // Valor por defecto si es obligatorio
            $em->persist($autor);
        }
        return $autor;
    }

    private function getOrCreateEditorial(string $nombre, EntityManagerInterface $em): Editorial
    {
        $editorial = $em->getRepository(Editorial::class)->findOneBy(['nombre' => $nombre]);
        if (!$editorial) {
            $editorial = new Editorial();
            $editorial->setNombre($nombre);
            $em->persist($editorial);
        }
        return $editorial;
    }
}