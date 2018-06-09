<?php

declare(strict_types=1);

namespace synapsepm\network\protocol\spp;

use pocketmine\utils\UUID;

class PlayerLoginPacket extends DataPacket {
	const NETWORK_ID = Info::PLAYER_LOGIN_PACKET;

	/** @var UUID */
	public $uuid;
	public $address;
	public $port;
	public $isFirstTime;
	public $cachedLoginPacket;

	public function encode() {
		$this->reset();
		$this->putUUID($this->uuid);
		$this->putString($this->address);
		$this->putUnsignedVarInt($this->port);
		$this->putInt($this->port);
		$this->putBool($this->isFirstTime ? (bool) 1 : (bool) 0);
		$this->putShort(count($this->cachedLoginPacket));
        $this->put($this->cachedLoginPacket);
	}

	public function decode() {
		$this->uuid = $this->getUUID();
		$this->address = $this->getString();
		$this->port = $this->getInt();
		$this->isFirstTime = $this->getBool() == 1;
		$this->cachedLoginPacket = $this->get($this->getShort());
	}
}