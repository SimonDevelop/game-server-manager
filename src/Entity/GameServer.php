<?php

namespace App\Entity;

use App\Repository\GameServerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use DateTimeInterface;

#[ORM\Entity(repositoryClass: GameServerRepository::class)]
#[ORM\Table(name: '`game_server`')]
class GameServer
{
    const STATE_TYPE = [
        0 => 'Off',
        1 => 'On',
        2 => 'Stopping',
        3 => 'Starting',
        4 => 'Updating',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $commandStart = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $commandUpdate = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $commandStop = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commandCustomInternal = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $path = null;

    #[ORM\Column(type: 'integer')]
    private ?int $stateType = null;

    #[ORM\ManyToOne(targetEntity: Server::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'id_server', referencedColumnName: 'id', nullable: true, onDelete: 'CASCADE')]
    private ?Server $server = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    /** @var Collection<int, User> */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'gameServers')]
    private Collection $users;

    /** @var Collection<int, Log> */
    #[ORM\OneToMany(mappedBy: 'gameServer', targetEntity: Log::class)]
    private Collection $logs;

    /** @var Collection<int, Cronjob> */
    #[ORM\OneToMany(mappedBy: 'gameServer', targetEntity: Cronjob::class)]
    private Collection $cronjobs;

    public function __construct()
    {
        $this->stateType = 0;
        $this->createdAt = new DateTimeImmutable();
        $this->users = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->cronjobs = new ArrayCollection();
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

    public function getCommandStop(): ?string
    {
        return $this->commandStop;
    }

    public function setCommandStop(string $commandStop): self
    {
        $this->commandStop = $commandStop;

        return $this;
    }

    public function getCommandCustomInternal(): ?string
    {
        return $this->commandCustomInternal;
    }

    public function setCommandCustomInternal(?string $commandCustomInternal): self
    {
        $this->commandCustomInternal = $commandCustomInternal;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
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

    public function getState(): ?string
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

    public function getCreatedAt(): DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addGameServer($this);
        }

        return $this;
    }

    public function removeUser(User $user): self
    {
        if ($this->users->removeElement($user)) {
            $user->removeGameServer($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Log>
     */
    public function getLogs(): Collection
    {
        return $this->logs;
    }

    public function addLog(Log $log): self
    {
        if (!$this->logs->contains($log)) {
            $this->logs->add($log);
            $log->setGameServer($this);
        }

        return $this;
    }

    public function removeLog(Log $log): self
    {
        if ($this->logs->removeElement($log)) {
            // set the owning side to null (unless already changed)
            if ($log->getGameServer() === $this) {
                $log->setGameServer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Cronjob>
     */
    public function getCronjobs(): Collection
    {
        return $this->cronjobs;
    }

    public function addCronjob(Cronjob $cronjob): self
    {
        if (!$this->cronjobs->contains($cronjob)) {
            $this->cronjobs->add($cronjob);
            $cronjob->setGameServer($this);
        }

        return $this;
    }

    public function removeCronjob(Cronjob $cronjob): self
    {
        if ($this->cronjobs->removeElement($cronjob)) {
            // set the owning side to null (unless already changed)
            if ($cronjob->getGameServer() === $this) {
                $cronjob->setGameServer(null);
            }
        }

        return $this;
    }
}
