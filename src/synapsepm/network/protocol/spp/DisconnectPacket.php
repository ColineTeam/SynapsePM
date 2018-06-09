<?php
namespace synapsepm\network\protocol\spp;

class DisconnectPacket extends SynapseDataPacket {
	const NETWORK_ID = SynapseInfo::DISCONNECT_PACKET;
	const TYPE_WRONG_PROTOCOL = 0;
	const TYPE_GENERIC = 1;

	public $type;
	public $message;

    public function pid() {
        return self::NETWORK_ID;
    }
	public function encode() {
		$this->reset();
		$this->putByte($this->type);
		$this->putString($this->message);
	}

	public function decode() {
		$this->type = $this->getByte();
            $this->message = $this->getString();
	}
}