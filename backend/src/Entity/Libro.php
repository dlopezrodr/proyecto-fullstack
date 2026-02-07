<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Metadata\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups; // Importante para ver los datos

#[ApiResource(
    normalizationContext: ['groups' => ['libro:read']],
    denormalizationContext: ['groups' => ['libro:write']]
)]
#[ORM\Entity]
#[ORM\Table(name: 'libro')]
class Libro
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: 'libro_id', type: 'integer')]
    #[Groups(['libro:read'])] // Esto permite que el ID se vea en el JSON
    private ?int $id = null; // Cambiado a $id para consistencia, mapeado a libro_id

    #[ORM\Column(length: 255, nullable: false)]
    #[Groups(['libro:read', 'libro:write'])]
    private ?string $titulo = null;

    #[ORM\Column(length: 13, unique: true, nullable: false)]
    #[Groups(['libro:read', 'libro:write'])]
    private ?string $isbn = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['libro:read', 'libro:write'])]
    private ?\DateTimeInterface $fecha_publicacion = null;

    #[ORM\ManyToOne(inversedBy: 'libros')]
    #[ORM\JoinColumn(name: 'editorial_id', referencedColumnName: 'editorial_id', nullable: false)]
    #[Groups(['libro:read', 'libro:write'])]
    private ?Editorial $editorial = null;

    #[ORM\ManyToOne(inversedBy: 'libros')]
    #[ORM\JoinColumn(name: 'autor_id', referencedColumnName: 'autor_id', nullable: false)]
    #[Groups(['libro:read', 'libro:write'])]
    private ?Autor $autor = null;
    
    // --- GETTERS Y SETTERS ---

    public function getId(): ?int // Cambiado para coincidir con el estándar
    {
        return $this->id;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): static
    {
        $this->titulo = $titulo;
        return $this;
    }

    public function getIsbn(): ?string
    {
        return $this->isbn;
    }

    public function setIsbn(string $isbn): static
    {
        $this->isbn = $isbn;
        return $this;
    }

    public function getFechaPublicacion(): ?\DateTimeInterface
    {
        return $this->fecha_publicacion;
    }

    public function setFechaPublicacion(?\DateTimeInterface $fecha_publicacion): static
    {
        $this->fecha_publicacion = $fecha_publicacion;
        return $this;
    }

    public function getEditorial(): ?Editorial
    {
        return $this->editorial;
    }

    public function setEditorial(?Editorial $editorial): static
    {
        $this->editorial = $editorial;
        return $this;
    }

    public function getAutor(): ?Autor
    {
        return $this->autor;
    }

    public function setAutor(?Autor $autor): static
    {
        $this->autor = $autor;
        return $this;
    }
}
