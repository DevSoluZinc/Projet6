<?php

namespace App\Entity;

use App\Repository\FiguresRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Comments;

#[ORM\Entity(repositoryClass: FiguresRepository::class)]
class Figures
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255,unique: true)]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\OneToMany(targetEntity: Comments::class, mappedBy: 'figure')]
    private Collection $comments;

    #[ORM\Column]
    private ?int $user_id = null;

    #[ORM\Column]
    private ?int $Group_id = null;

    #[ORM\Column(length: 255)]
    private ?string $movie = null;

    #[ORM\Column(type: Types::JSON)]
    private array $illustrations = [];

    public function __construct()
    {
        $this->comments = new ArrayCollection();
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): static
    {
        $this->user_id = $user_id;

        return $this;
    }


    public function getGroupId(): ?int
    {
        return $this->Group_id;
    }

    public function setGroupId(int $Group_id): static
    {
        $this->Group_id = $Group_id;

        return $this;
    }

    public function getMovie(): ?string
    {
        return $this->movie;
    }

    public function setMovie(string $movie): static
    {
        $this->movie = $movie;

        return $this;
    }

    public function getIllustrations(): array
    {
        return $this->illustrations;
    }

    public function setIllustrations(?array $illustrations): static
    {
       
        $this->illustrations = $illustrations;
    
        return $this;
    }
}
