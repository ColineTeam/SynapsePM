<?php
namespace synapsepm\network\protocol\spp;

use pocketmine\utils\UUID;

class RedirectPacket extends SynapseDataPacket {
	const NETWORK_ID = SynapseInfo::REDIRECT_PACKET;
	/** @var UUID */
	public $uuid;
	public $direct;
	public $mcpeBuffer;

    public function pid() {
        return self::NETWORK_ID;
    }

	public function encode() {
		$this->reset();
		$this->putUUID($this->uuid);
		$this->putBool($this->direct);
		$this->putUnsignedVarInt(strlen($this->mcpeBuffer));
		$this->put($this->mcpeBuffer);
	}

	public function decode() {
		$this->uuid = $this->getUUID();
		$this->direct = $this->getBool();
		$this->mcpeBuffer = $this->get($this->getUnsignedVarInt());
	}
}