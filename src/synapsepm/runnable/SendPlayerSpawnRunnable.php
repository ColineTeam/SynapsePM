<?php

namespace synapsepm\runnable;

use pocketmine\network\protocol\PlayStatusPacket;
use synapsepm\SynapsePlayer;
use pocketmine\scheduler\Task;

/**
 * Created by boybook on 16/9/26.
 */
class SendPlayerSpawnRunnable extends Task {

    private $player;

    public function __construct(SynapsePlayer $player) {
        $this->player = $player;
    }

    public function run($currentTick) {
        $statusPacket0 = new PlayStatusPacket();
        $statusPacket0->status = PlayStatusPacket::PLAYER_SPAWN;
        $this->player->dataPacket($statusPacket0);
    }
}
