<?php

namespace App\Entity;

use App\Repository\ServerRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;
use DateTimeInterface;

#[ORM\Entity(repositoryClass: ServerRepository::class)]
#[ORM\Table(name: '`server`')]
class Server
{
    private string $cipher = 'aes-128-cbc';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: 'string', length: 16)]
    private ?string $ip = null;

    #[ORM\Column(type: 'string', length: 5)]
    private ?string $port = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $login = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $password = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $lastConnection = null;

    #[ORM\Column(type: 'datetime')]
    private \DateTimeInterface $createdAt;

    public function __construct()
    {
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

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getPort(): ?string
    {
        return $this->port;
    }

    public function setPort(string $port): self
    {
        $this->port = $port;

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getPassword(): ?string
    {
        return openssl_decrypt(
            $this->password,
            $this->cipher,
            $_ENV['PASSWORD_HASH_KEY'],
            0,
            $_ENV['IV_HASH']
        );
    }

    public function setPassword(?string $password): self
    {
        if (null === $password) {
            $this->password = $this->password;
        } else {
            $this->password = openssl_encrypt(
                $password,
                $this->cipher,
                $_ENV['PASSWORD_HASH_KEY'],
                0,
                $_ENV['IV_HASH']
            );
        }

        return $this;
    }

    public function getLastConnection(): ?DateTimeInterface
    {
        return $this->lastConnection;
    }

    public function setLastConnection(?DateTimeInterface $lastConnection): self
    {
        $this->lastConnection = $lastConnection;

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
}
