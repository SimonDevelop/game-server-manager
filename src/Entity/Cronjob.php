<?php

namespace App\Entity;

use App\Repository\CronjobRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CronjobRepository::class)]
class Cronjob
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    private ?string $periodicity = null;

    #[ORM\Column(length: 255)]
    private ?string $comment = null;

    #[ORM\ManyToOne(inversedBy: 'cronjobs')]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    private ?GameServer $gameServer;

    public function __construct()
    {
        $this->gameServer = null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getPeriodicity(): ?string
    {
        return $this->periodicity;
    }

    public function setPeriodicity(string $periodicity): static
    {
        $this->periodicity = $periodicity;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(string $comment): static
    {
        $this->comment = $comment;

        return $this;
    }

    public function getGameServer(): ?GameServer
    {
        return $this->gameServer;
    }

    public function setGameServer(?GameServer $gameServer): self
    {
        $this->gameServer = $gameServer;

        return $this;
    }
}
