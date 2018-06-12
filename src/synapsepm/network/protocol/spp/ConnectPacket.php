<?php
namespace synapsepm\network\protocol\spp;

class ConnectPacket extends SynapseDataPacket {

    const NETWORK_ID = SynapseInfo::CONNECT_PACKET;
    public $protocol = SynapseInfo::CURRENT_PROTOCOL;
    public $maxPlayers;
    public $isMainServer;
    public $description;
    public $password;

    public function pid() {
        return self::NETWORK_ID;
    }

    public function encode() {
        $this->reset();
        $this->putInt($this->protocol);
        $this->putInt($this->maxPlayers);
        $this->putBool($this->isMainServer);
        $this->putString($this->description);
        $this->putString($this->password);
    }

    public function decode() {
        $this->protocol = $this->getInt();
        $this->maxPlayers = $this->getInt();
        $this->isMainServer = $this->getByte() == 1;
        $this->description = $this->getString();
        $this->password = $this->getString();
    }
}
