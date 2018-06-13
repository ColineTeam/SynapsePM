<?php

namespace synapsepm\event\player;

use pocketmine\event\HandlerList;
use pocketmine\network\SourceInterface;
use synapsepm\SynapsePlayer;
use synapsepm\event\SynapseEvent;


class SynapsePlayerCreationEvent extends SynapseEvent {

    public static $handlerList = null;
    private $interfaz;
    private $clientId;
    private $address;
    private $port;
    private $baseClass;
    private $playerClass;
    
    public function __construct(SourceInterface $interfaz, $baseClass, $playerClass, $address, $port) {
        $this->interfaz = $interfaz;
        $this->clientId = mt_rand();
        $this->address = $address;
        $this->port = $port;

        $this->baseClass = $baseClass;
        $this->playerClass = $playerClass;
    }

    public function getInterface() {
        return $this->interfaz;
    }

    public function getAddress() {
        return $this->address;
    }

    public function getPort() {
        return $this->port;
    }

    public function getClientId() {
        return $this->clientId;
    }

    public function getBaseClass() {
        return $this->baseClass;
    }

    public function setBaseClass($baseClass) {
        $this->baseClass = $baseClass;
    }

    public function getPlayerClass() {
        return $this->playerClass;
    }

    public function setPlayerClass($playerClass) {
        $this->playerClass = $playerClass;
    }
}
