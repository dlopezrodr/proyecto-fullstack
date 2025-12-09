<?php

namespace App\Controller;

use App\Entity\Autor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

// 💥 RUTA BASE: Coincide con el endpoint de Angular /api/autores
#[Route('/api/autores')] 
final class AutorController extends AbstractController
{
    // ===============================================
    // R - READ (GET /api/autores) - Obtener todos
    // ===============================================
    #[Route(name: 'api_autores_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $autores = $entityManager
            ->getRepository(Autor::class)
            ->findAll();

        // Serializa el array de objetos Autor a JSON
        return $this->json($autores, Response::HTTP_OK);
    }

    // ===============================================
    // C - CREATE (POST /api/autores) - Crear nuevo
    // ===============================================
    #[Route(name: 'api_autores_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // 1. Obtener y decodificar el cuerpo JSON
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json(['message' => 'Invalid JSON body.'], Response::HTTP_BAD_REQUEST);
        }

        // 2. Crear y llenar la entidad
        $autor = new Autor();
        
        // 💥 IMPORTANTE: Ajusta las claves ('name', 'firstName', 'lastName') 
        // a las propiedades reales de tu entidad Autor.
        $autor->setNombre($data['nombre'] ?? null); 
        $autor->setApellido($data['apellido'] ?? null); 
        // Añade cualquier otro campo necesario aquí...

        // 3. Persistir
        $entityManager->persist($autor);
        $entityManager->flush();

        // 4. Devolver el objeto creado con código 201 Created
        return $this->json($autor, Response::HTTP_CREATED);
    }

    // ===============================================
    // R - READ (GET /api/autores/{id}) - Obtener uno
    // ===============================================
    #[Route('/{id}', name: 'api_autor_show', methods: ['GET'])]
    public function show(Autor $autor): Response
    {
        // Symfony convierte automáticamente el {id} de la URL en el objeto Autor
        return $this->json($autor, Response::HTTP_OK);
    }

    // ===============================================
    // U - UPDATE (PUT /api/autores/{id}) - Actualizar
    // ===============================================
    #[Route('/{id}', name: 'api_autor_edit', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, Autor $autor, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json(['message' => 'Invalid JSON body.'], Response::HTTP_BAD_REQUEST);
        }

        // 💥 Llenar la entidad solo si el dato existe en la petición
        if (isset($data['nombre'])) {
            $autor->setNombre($data['nombre']);
        }
        if (isset($data['apellido'])) {
            $autor->setApellido($data['apellido']);
        }
        // ... (otros campos)

        $entityManager->flush();

        // Devolver el objeto actualizado
        return $this->json($autor, Response::HTTP_OK);
    }

    // ===============================================
    // D - DELETE (DELETE /api/autores/{id}) - Eliminar
    // ===============================================
    #[Route('/{id}', name: 'api_autor_delete', methods: ['DELETE'])]
    public function delete(Autor $autor, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($autor);
        $entityManager->flush();

        // Devolver una respuesta sin contenido (204 No Content) para indicar éxito
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}