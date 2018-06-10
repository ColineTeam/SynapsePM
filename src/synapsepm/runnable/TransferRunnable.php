<?php

namespace synapsepm\runnable;

use synapsepm\SynapsePlayer;
use synapsepm\network\protocol\spp\TransferPacket;
use pocketmine\scheduler\Task

/**
 * Created by boybook on 16/9/26.
 */
class TransferRunnable extends Task{

    private $player;
    private $hash;

    public function __construct(SynapsePlayer $player, $hash) {
        $this->player = $player;
        $this->hash = $hash;
    }
    
    public function run($currentTick) {
        $pk = new TransferPacket();
        $pk->uuid = $this->player->getUniqueId();
        $pk->clientHash = $hash;
        $this->player->getSynapseEntry()->sendDataPacket($pk);
    }
}
