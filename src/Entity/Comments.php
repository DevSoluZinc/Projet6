<?php

namespace App\Entity;

use App\Repository\CommentsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CommentsRepository::class)]
class Comments
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $figure_id = null;




    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id")]
    private User $user;

    #[ORM\Column(length: 255)]
    private ?string $content = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $created_at = null;
    #[ORM\ManyToOne(targetEntity: Figures::class, inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'figure_id', referencedColumnName: 'id')]
    private Figures $figure;

    public function getFigure(): Figures
    {
        return $this->figure;
    }
    public function setFigure(Figures $figure): self
    {
        $this->figure = $figure;
        return $this;
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFigureId(): ?int
    {
        return $this->figure_id;
    }

    public function setFigureId(int $figure_id): static
    {
        $this->figure_id = $figure_id;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeImmutable $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }
    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
