<?php

namespace App\Message;

class SendCommandMessage
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var array
     */
    private $informations;

    /**
     * @var string
     */
    private $command;

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

    public function getInformations(): array
    {
        return $this->informations;
    }

    public function getCommand(): string
    {
        return $this->command;
    }
}
