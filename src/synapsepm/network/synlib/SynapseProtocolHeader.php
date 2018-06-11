<?php

namespace synapsepm\network\synlib;

/**
 * SynapseProtocolHeader
 * ===============
 * author: boybook
 * Synapse Protocol Header
 * nemisys
 * ===============
 */
class SynapseProtocolHeader {

    /**
     * Head Length
     */
    const HEAD_LENGTH = 7;

    /**
     * Magic
     */
    const MAGIC = 0xbabe;

    private $pid;
    private $bodyLength;

    public function pid($pid = null) {
        if($pid != null) $this->pid = $pid;
        else return $this->pid;
    }

    public function bodyLength($bodyLength = null) {
        if($bodyLength != null) $this->bodyLength = $bodyLength;
        else return $this->bodyLength;
    }

}
