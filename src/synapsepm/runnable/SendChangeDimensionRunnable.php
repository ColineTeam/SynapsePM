<?php

namespace synapsepm\runnable;

use synapsepm\SynapsePlayer;
use pocketmine\network\protocol\ChangeDimensionPacket;
use pocketmine\scheduler\Task;

/**
 * Created by boybook on 16/9/26.
 */
 
class SendChangeDimensionRunnable extends Task{

    private $player;
    private $dimension;

    public function __construct(SynapsePlayer $player, $dimension) {
        $this->player = $player;
        $this->dimension = $dimension;
    }

    public function run($currentTick) {
        $changeDimensionPacket1 = new ChangeDimensionPacket();
        $changeDimensionPacket1->dimension = $this->dimension;
        $changeDimensionPacket1->x = (float) $player->getX();
        $changeDimensionPacket1->y = (float) $player->getY() + $player->getEyeHeight();
        $changeDimensionPacket1->z = (float) $player->getZ();
        $player->dataPacket($changeDimensionPacket1);
    }
}
