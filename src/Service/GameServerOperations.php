<?php

namespace App\Service;

use App\Entity\GameServer;

class GameServerOperations
{
    public function getGameServerNameScreen(GameServer $game): string
    {
        return 'gameserver_'.$game->getId();
    }
}
