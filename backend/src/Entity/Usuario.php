<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity]
#[ORM\Table(name: 'usuario')]
// Implementar las interfaces de seguridad
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: 'user_id', type: 'integer')]
    private ?int $user_id = null;

    #[ORM\Column(length: 50, unique: true, nullable: false)]
    private ?string $username = null;

    #[ORM\Column(length: 255, unique: true, nullable: false)]
    private ?string $email = null;

    #[ORM\Column(length: 255, nullable: false)]
    private ?string $password_hash = null;

    // AÑADIDO: Campo para almacenar los roles del usuario (en formato JSON)
    #[ORM\Column(type: 'json')]
    private array $roles = [];


    // --- MÉTODOS REQUERIDOS POR LAS INTERFACES DE SEGURIDAD ---

    /**
     * Retorna la colección de roles que tiene el usuario.
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Se garantiza que todo usuario autenticado tenga ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Retorna el identificador único del usuario (usado para login).
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        // Se utiliza el 'username' como identificador único para iniciar sesión
        return (string) $this->username;
    }

    /**
     * Retorna el hash de la contraseña almacenado en la base de datos.
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password_hash;
    }
    
    /**
     * Limpia los datos sensibles o temporales almacenados en la sesión.
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // Si usaras un campo temporal para contraseñas sin cifrar, se limpiaría aquí.
        // En este caso, no es necesario hacer nada.
    }
    
    // --- GETTERS y SETTERS EXISTENTES ---

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }
    
    // Cambiado el nombre del getter para ser más estándar, aunque sigue usando $password_hash
    public function getPasswordHash(): ?string
    {
        return $this->password_hash;
    }

    public function setPasswordHash(string $password_hash): static
    {
        $this->password_hash = $password_hash;
        return $this;
    }
}