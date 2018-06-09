<?php

namespace synapsepm.network.protocol.spp;

use pocketmine\utils\UUID;

/**
 * Created by boybook on 16/6/24.
 */
class TransferPacket extends SynapseDataPacket {

    public static final $NETWORK_ID = SynapseInfo::TRANSFER_PACKET;
    public $uuid;
    public $clientHash;

    public function pid() {
        return self::$NETWORK_ID;
    }

    public function encode() {
        $this->reset();
        $this->putUUID($this->uuid);
        $this->putString($this->clientHash);
    }

    public function decode() {
        $this->uuid = $this->getUUID();
        $this->clientHash = $this->getString();
    }
}
