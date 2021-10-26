<?php

namespace App\Entity;

use App\Repository\GameServerRepository;
use App\Entity\Server;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

/**
 * @ORM\Entity(repositoryClass=GameServerRepository::class)
 */
class GameServer
{
    const GAME_TYPE = [
        0 => "Screen Server",
        1 => "Docker Server"
    ];

    const STATE_TYPE = [
        0 => 'Off',
        1 => 'On',
        2 => 'Installing',
        3 => 'Starting',
        4 => 'Stopping',
        5 => 'Updating'
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $commandStart;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $commandUpdate;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $commandStop;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\Column(type="integer")
     */
    private $gameType;

    /**
     * @ORM\Column(type="integer")
     */
    private $stateType;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Server", cascade={"persist"})
     * @ORM\JoinColumn(name="id_server", referencedColumnName="id", nullable=true)
     */
    private $server;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function __construct()
    {
        $this->stateType = 0;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCommandStart(): ?string
    {
        return $this->commandStart;
    }

    public function setCommandStart(string $commandStart): self
    {
        $this->commandStart = $commandStart;

        return $this;
    }

    public function getCommandUpdate(): ?string
    {
        return $this->commandUpdate;
    }

    public function setCommandUpdate(?string $commandUpdate): self
    {
        $this->commandUpdate = $commandUpdate;

        return $this;
    }

    public function getCommandStop(): string
    {
        return $this->commandStop;
    }

    public function setCommandStop(string $commandStop): self
    {
        $this->commandStop = $commandStop;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getGameType(): ?int
    {
        return $this->gameType;
    }

    public function setGameType(int $gameType): self
    {
        $this->gameType = $gameType;

        return $this;
    }

    public function getGame(): string
    {
        return self::GAME_TYPE[$this->gameType];
    }

    public function getStateType(): ?int
    {
        return $this->stateType;
    }

    public function setStateType(int $stateType): self
    {
        $this->stateType = $stateType;

        return $this;
    }

    public function getState(): string
    {
        return self::STATE_TYPE[$this->stateType];
    }

    public function getServer(): ?Server
    {
        return $this->server;
    }

    public function setServer(?Server $server): self
    {
        $this->server = $server;

        return $this;
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
}
