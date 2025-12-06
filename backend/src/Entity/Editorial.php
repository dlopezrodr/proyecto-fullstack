<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'editorial')]
class Editorial
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: "IDENTITY")]
    #[ORM\Column(name: 'editorial_id', type: 'integer')]
    private ?int $editorial_id = null;

    #[ORM\Column(length: 255, unique: true, nullable: false)]
    private ?string $nombre = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $pais = null;

    #[ORM\OneToMany(mappedBy: 'editorial', targetEntity: Libro::class)]
    private Collection $libros;

    public function __construct()
    {
        $this->libros = new ArrayCollection();
    }

    // Getters y Setters...
    public function getEditorialId(): ?int
    {
        return $this->editorial_id;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }

    public function setNombre(string $nombre): static
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getPais(): ?string
    {
        return $this->pais;
    }

    public function setPais(?string $pais): static
    {
        $this->pais = $pais;
        return $this;
    }

    /**
     * @return Collection<int, Libro>
     */
    public function getLibros(): Collection
    {
        return $this->libros;
    }

    public function addLibro(Libro $libro): static
    {
        if (!$this->libros->contains($libro)) {
            $this->libros->add($libro);
            $libro->setEditorial($this);
        }
        return $this;
    }

    public function removeLibro(Libro $libro): static
    {
        if ($this->libros->removeElement($libro)) {
            // set the owning side to null (unless already changed)
            if ($libro->getEditorial() === $this) {
                $libro->setEditorial(null);
            }
        }
        return $this;
    }
}