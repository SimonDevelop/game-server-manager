<?php

namespace App\Message;

class SendCommandMessage
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $command;

    public function __construct(int $id, string $command)
    {
        $this->id      = $id;
        $this->command = $command;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCommand(): string
    {
        return $this->command;
    }
}
