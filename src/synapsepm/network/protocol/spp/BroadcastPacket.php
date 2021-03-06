<?php
namespace synapsepm\network\protocol\spp;

use pocketmine\utils\UUID;


class BroadcastPacket extends SynapseDataPacket {
	const NETWORK_ID = SynapseInfo::BROADCAST_PACKET;

	/** @var UUID[] */
	public $entries = [];
	public $direct;
	public $payload;

    public function pid() {
        return self::NETWORK_ID;
    }
	public function encode() {
		$this->reset();
		$this->putBool($this->direct);
		$this->putShort(count($this->entries));
		foreach ($this->entries as $uuid) {
			$this->putUUID($uuid);
		}
            $this->putString($this->payload);
	}

	public function decode() {
		$this->direct = $this->getBool();
		$len = $this->getShort();
		for ($i = 0; $i < $len; $i++) {
                $this->entries[] = $this->getUUID();
		}
            $this->payload = $this->getString();
	}
}
