<?php

namespace App\Service;

use App\Entity\GameServer;

class GameServerOperations
{
    public static function getGameServerNameScreen(GameServer $game): string
    {
        return strtolower(str_replace(' ', '', $game->getName())).'_'.$game->getId();
    }
}
