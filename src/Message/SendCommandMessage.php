<?php

namespace App\Message;

class SendCommandMessage
{
    private int $id;

    /**
     * @var array<mixed> $informations
     */
    private array $informations;

    private string $command;

    /**
     * @param array<mixed> $informations
     */
    public function __construct(int $id, array $informations, string $command)
    {
        $this->id           = $id;
        $this->informations = $informations;
        $this->command      = $command;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return mixed[]
     */
    public function getInformations(): array
    {
        return $this->informations;
    }

    public function getCommand(): string
    {
        return $this->command;
    }
}
