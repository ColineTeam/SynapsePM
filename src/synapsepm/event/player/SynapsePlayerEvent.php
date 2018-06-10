<?php

namespace synapsepm\event\player;

use synapsepm\SynapsePlayer;
use synapsepm\event\SynapseEvent;

/**
 * Created by boybook on 16/6/25.
 */
abstract class SynapsePlayerEvent extends SynapseEvent {

    protected $player;

    public function getPlayer() {
        return $this->player;
    }
}
