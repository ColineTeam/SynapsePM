<?php

namespace synapsepm\network\protocol\spp;

use pocketmine\utils\BinaryStream;

abstract class SynapseDataPacket extends BinaryStream {

    public $isEncoded = false;

    abstract public function pid();

    abstract public function decode();

    abstract public function encode();

    public function reset() {
        parent::reset();
    }

    public function clean() {
        $this->setBuffer(null);

        $this->isEncoded = false;
        $this->offset = 0;
        return $this;
    }

    public function clone() {
        try {
            return new parent;
        } catch (Exception $e) {
            return null;
        }
    }

}
