<?php

namespace synapsepm\event\player;

use pocketmine\event\Cancellable;
use synapsepm\SynapsePlayer;

class SynapsePlayerConnectEvent extends SynapsePlayerEvent implements Cancellable {

    public static $handlerList = null;
    private $firstTime;

    public function __construct(SynapsePlayer $player, $firstTime = true) {
        $this->player = $player;
        $this->firstTime = $firstTime;
    }

    public function isFirstTime() {
        return $this->firstTime;
    }
}
