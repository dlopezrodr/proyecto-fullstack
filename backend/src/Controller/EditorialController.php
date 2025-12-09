<?php

namespace App\Controller;

use App\Entity\Editorial;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

// 💥 RUTA BASE: Coincide con el endpoint de Angular /api/editoriales
#[Route('/api/editoriales')] 
final class EditorialController extends AbstractController
{
    // ===============================================
    // R - READ (GET /api/editoriales) - Obtener todas
    // ===============================================
    #[Route(name: 'api_editoriales_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $editoriales = $entityManager
            ->getRepository(Editorial::class)
            ->findAll();

        // Serializa el array de objetos Editorial a JSON
        return $this->json($editoriales, Response::HTTP_OK);
    }

    // ===============================================
    // C - CREATE (POST /api/editoriales) - Crear nueva
    // ===============================================
    #[Route(name: 'api_editoriales_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // 1. Obtener y decodificar el cuerpo JSON
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json(['message' => 'Invalid JSON body.'], Response::HTTP_BAD_REQUEST);
        }

        // 2. Crear y llenar la entidad
        $editorial = new Editorial();
        
        // 💥 IMPORTANTE: Ajusta las claves ('nombre', 'pais') 
        // a las propiedades reales de tu entidad Editorial.
        $editorial->setNombre($data['nombre'] ?? null); 
        $editorial->setPais($data['pais'] ?? null); 
        // Añade cualquier otro campo necesario aquí...

        // 3. Persistir
        $entityManager->persist($editorial);
        $entityManager->flush();

        // 4. Devolver el objeto creado con código 201 Created
        return $this->json($editorial, Response::HTTP_CREATED);
    }

    // ===============================================
    // R - READ (GET /api/editoriales/{id}) - Obtener una
    // ===============================================
    #[Route('/{id}', name: 'api_editorial_show', methods: ['GET'])]
    public function show(Editorial $editorial): Response
    {
        // Symfony convierte automáticamente el {id} de la URL en el objeto Editorial
        return $this->json($editorial, Response::HTTP_OK);
    }

    // ===============================================
    // U - UPDATE (PUT /api/editoriales/{id}) - Actualizar
    // ===============================================
    #[Route('/{id}', name: 'api_editorial_edit', methods: ['PUT', 'PATCH'])]
    public function edit(Request $request, Editorial $editorial, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            return $this->json(['message' => 'Invalid JSON body.'], Response::HTTP_BAD_REQUEST);
        }

        // 💥 Llenar la entidad solo si el dato existe en la petición
        if (isset($data['nombre'])) {
            $editorial->setNombre($data['nombre']);
        }
        if (isset($data['pais'])) {
            $editorial->setPais($data['pais']);
        }
        // ... (otros campos)

        $entityManager->flush();

        // Devolver el objeto actualizado
        return $this->json($editorial, Response::HTTP_OK);
    }

    // ===============================================
    // D - DELETE (DELETE /api/editoriales/{id}) - Eliminar
    // ===============================================
    #[Route('/{id}', name: 'api_editorial_delete', methods: ['DELETE'])]
    public function delete(Editorial $editorial, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($editorial);
        $entityManager->flush();

        // Devolver una respuesta sin contenido (204 No Content) para indicar éxito
        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}