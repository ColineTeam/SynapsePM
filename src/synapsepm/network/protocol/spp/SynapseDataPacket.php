<?php
namespace synapsepm\network\protocol\spp;

use pocketmine\network\mcpe\NetworkBinaryStream;

abstract class SynapseDataPacket extends NetworkBinaryStream {

    public $isEncoded = false;

    abstract public function pid();

    abstract public function decode();

    abstract public function encode();

    public function reset() {
        $this->buffer = chr($this::NETWORK_ID);
        $this->offset = 0;
    }

    public function clone() {
        try {
            return new parent;
        } catch (Exception $e) {
            return null;
        }
    }

}
