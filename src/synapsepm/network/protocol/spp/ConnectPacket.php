<?php
namespace synapsepm\network\protocol\spp;

class ConnectPacket extends SynapseDataPacket {

    const NETWORK_ID = SynapseInfo::CONNECT_PACKET;
    public $protocol = SynapseInfo::CURRENT_PROTOCOL;
    public $maxPlayers;
    public $isMainServer;
    public $isLobbyServer = true;
    public $transferShutdown = false;
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
        $this->putBool($this->isLobbyServer);
        $this->putBool($this->transferShutdown);
        $this->putString($this->description);
        $this->putString($this->password);
    }

    public function decode() {
        $this->protocol = $this->getInt();
        $this->maxPlayers = $this->getInt();
        $this->isMainServer = $this->getBool();
        $this->isLobbyServer = $this->getBool();
        $this->transferShutdown = $this->getBool();
        $this->description = $this->getString();
        $this->password = $this->getString();
    }
}
