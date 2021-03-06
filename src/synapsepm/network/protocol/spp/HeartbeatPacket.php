<?php
namespace synapsepm\network\protocol\spp;

class HeartbeatPacket extends SynapseDataPacket {
    const NETWORK_ID = SynapseInfo::HEARTBEAT_PACKET;

    public $tps;
    public $load;
    public $upTime;

    public function pid() {
        return self::NETWORK_ID;
    }

    public function encode() {
        $this->reset();
        $this->putFloat($this->tps);
        $this->putFloat($this->load);
        $this->putLong($this->upTime);
    }

    public function decode() {
        $this->tps = $this->getFloat();
        $this->load = $this->getFloat();
        $this->upTime = $this->getLong();
    }
}