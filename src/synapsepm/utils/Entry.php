<?php

namespace synapsepm\utils;

class Entry {
    private $ip;
    private $port;
    private $playerCount;
    private $maxPlayers;
    private $description;

    public function getIp() {
        return $this->ip;
    }

    public function getPort() {
        return $this->port;
    }

    public function getMaxPlayers() {
        return $this->maxPlayers;
    }

    public function getPlayerCount() {
        return $this->playerCount;
    }

    public function getDescription() {
        return $this->description;
    }
}
