<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    /**
     * Esta ruta es el punto de anclaje para el login basado en JWT.
     * El firewall de seguridad configurado en security.yaml intercepta
     * las peticiones POST a esta URL ANTES de que lleguen a este método.
     * El cuerpo del método NUNCA DEBE SER EJECUTADO en un login exitoso.
     */
    

    /**
     * El Logout en JWT es manejado por el frontend (eliminando el token).
     * Esta ruta es opcional para la API, ya que no hace nada en el backend.
     */
    #[Route(path: '/api/logout', name: 'app_logout', methods: ['POST'])]
    public function logout(): void
    {
        // En una API, el logout es un proceso puramente del lado del cliente.
        // Mantenemos la ruta para el futuro, aunque no haga nada en el backend.
    }
}