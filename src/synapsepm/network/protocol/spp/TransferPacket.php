<?php
namespace synapseapi\network\protocol\spp;

use pocketmine\utils\UUID;

class TransferPacket extends SynapseDataPacket {

    public static $NETWORK_ID = SynapseInfo::TRANSFER_PACKET;
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
