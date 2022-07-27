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
    private $cipher = 'aes-128-cbc';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 16)]
    private $ip;

    #[ORM\Column(type: 'string', length: 5)]
    private $port;

    #[ORM\Column(type: 'string', length: 255)]
    private $login;

    #[ORM\Column(type: 'string', length: 255)]
    private $password;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $lastConnection;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

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
