<?php

namespace synapsepm\event\client;

use pocketmine\event\Cancellable;
use pocketmine\event\HandlerList;
use synapsepm\SynapseEntry;
use synapsepm\event\SynapseEvent;
use synapsepm\network\protocol\spp\SynapseDataPacket;

/**
 * @author CreeperFace
 */
class SynapseDataPacketSendEvent extends SynapseEvent implements Cancellable {

    public static handlerList = null;
    private final $packet;
    private final $entry;

    public function __construct(SynapseEntry $entry, SynapseDataPacket $packet) {
        $this->packet = $packet;
        $this->entry = $entry;
    }

    public function getPacket() {
        return $this->packet;
    }
}
