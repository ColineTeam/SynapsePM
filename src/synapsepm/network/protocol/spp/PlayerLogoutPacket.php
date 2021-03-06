<?php
namespace synapsepm\network\protocol\spp;

use pocketmine\utils\UUID;


class PlayerLogoutPacket extends SynapseDataPacket {
	const NETWORK_ID = SynapseInfo::PLAYER_LOGOUT_PACKET;

	/** @var UUID */
	public $uuid;
	public $reason;

    public function pid() {
        return self::NETWORK_ID;
    }

	public function encode() {
		$this->reset();
		$this->putUUID($this->uuid);
		$this->putString($this->reason);
	}

	public function decode() {
		$this->uuid = $this->getUUID();
		$this->reason = $this->getString();
	}
}