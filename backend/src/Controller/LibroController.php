<?php

namespace App\Controller;

use App\Entity\Libro;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse; // Necesario para devolver JSON

// 💥 RUTA BASE: Coincide con el endpoint de Angular /api/libros
#[Route('/api/libros')] 
final class LibroController extends AbstractController
{
    // ===============================================
    // R - READ (GET /api/libros) - Obtener todos
    // ===============================================
    #[Route(name: 'api_libros_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $libros = $entityManager
            ->getRepository(Libro::class)
            ->findAll();

        // Serializa el array de objetos Libro a JSON
        // Nota: Los nombres de las propiedades se usarán como claves JSON (ej. "title").
        return $this->json($libros, Response::HTTP_OK);
    }

    // ===============================================
    // C - CREATE (POST /api/libros) - Crear nuevo
    // ===============================================
    #[Route(name: 'api_libros_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // 1. Obtener y decodificar el cuerpo JSON
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json(['message' => 'Invalid JSON body.'], Response::HTTP_BAD_REQUEST);
        }

        // 2. Crear y llenar la entidad
        $libro = new Libro();
        
        // 💥 IMPORTANTE: Ajusta los nombres de las claves ('title', 'author', etc.) 
        // a cómo los envía tu formulario de Angular.
        // Y ajusta los métodos set* a los que existan en tu entidad Libro.
        $libro->setTitulo($data['title'] ?? null); 
        $libro->setAutor($data['author'] ?? null); 
        $libro->setIsbn($data['isbn'] ?? null); 
        // Añade cualquier otro campo necesario aquí...

        // 3. Persistir
        $entityManager->persist($libro);
        $entityManager->flush();

        // 4. Devolver el objeto creado con código 201 Created
        return $this->json($libro, Response::HTTP_CREATED);
    }

    // ===============================================
    // R - READ (GET /api/libros/{id}) - Obtener uno
    // ===============================================
    #[Route('/{id}', name: 'api_libro_show', methods: ['GET'])]
    public function show(Libro $libro): Response
    {
        // Symfony convierte automáticamente el {id} de la URL en el objeto Libro (Param Converter)
        return $this->json($libro, Response::HTTP_OK);
    }

    // ===============================================
    // U - UPDATE (PUT /api/libros/{id}) - Actualizar
    // ===============================================
    #[Route('/{id}', name: 'api_libro_edit', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, Libro $libro, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json(['message' => 'Invalid JSON body.'], Response::HTTP_BAD_REQUEST);
        }

        // 💥 Llenar la entidad solo si el dato existe en la petición (permite actualizaciones parciales)
        if (isset($data['title'])) {
            $libro->setTitulo($data['title']);
        }
        if (isset($data['author'])) {
            $libro->setAutor($data['author']);
        }
        if (isset($data['isbn'])) {
            $libro->setIsbn($data['isbn']);
        }
        // ... (otros campos)

        $entityManager->flush();

        // Devolver el objeto actualizado
        return $this->json($libro, Response::HTTP_OK);
    }

    // ===============================================
    // D - DELETE (DELETE /api/libros/{id}) - Eliminar
    // ===============================================
    #[Route('/{id}', name: 'api_libro_delete', methods: ['DELETE'])]
    public function delete(Libro $libro, EntityManagerInterface $entityManager): Response
    {
        // 💥 Usamos el verbo DELETE, no se necesita el token CSRF.
        $entityManager->remove($libro);
        $entityManager->flush();

        // Devolver una respuesta sin contenido (204 No Content) para indicar éxito
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}