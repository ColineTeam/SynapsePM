<?php
namespace synapsepm\network\protocol\spp;

use pocketmine\utils\UUID;


class FastPlayerListPacket extends SynapseDataPacket {
    const NETWORK_ID = SynapseInfo::FAST_PLAYER_LIST_PACKET;
    const TYPE_ADD = 0;
    const TYPE_REMOVE = 1;

    /** @var UUID */
    public $sendTo;
    /** @var array[] */
    public $entries = [];
    public $type;

    public function pid() {
        return self::NETWORK_ID;
    }
    public function decode() {
    }

    public function encode() {
        $this->reset();
        $this->putUUID($this->sendTo);
        $this->putByte($this->type);
        $this->putInt(count($this->entries));
        foreach ($this->entries as $d) {
            if ($this->type === self::TYPE_ADD) {
                $this->putUUID($d[0]);
                $this->putLong($d[1]);
                $this->putString($d[2]);
            } else {
                $this->putUUID($d[0]);
            }
        }
    }
}
