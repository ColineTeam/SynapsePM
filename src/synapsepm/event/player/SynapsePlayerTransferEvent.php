<?php

namespace synapsepm\event\player;

use pocketmine\event\Cancellable;
use pocketmine\event\HandlerList;
use synapsepm\SynapsePlayer;
use synapsepm\utils\ClientData\Entry;

/**
 * @author CreeperFace
 */
class SynapsePlayerTransferEvent extends SynapsePlayerEvent implements Cancellable {

    public static $handlerList = null;
    private final $clientData;
    
    public function __construct(SynapsePlayer $player, Entry $data){
        $this->player = $player;
        $this->clientData = $data;
    }

    public function getClientData() {
        return $this->clientData;
    }
}
