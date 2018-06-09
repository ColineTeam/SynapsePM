<?php
namespace synapsepm\network;

use pocketmine\Server;
use synapsepm\network\synlib\SynapseClient;
use synapsepm\SynapseAPI;
use synapsepm\SynapseEntry;

class SynapseInterface {
    /** @var DataPacket[] */
    private $packetPool = [];
    /** @var SynapseEntry */
    private $synapse;
    /** @var SynapseClient */
    private $client;
    private $connected = false;
    /* @var SynapseEntryPutPacketThread */
    private $putPacketThread;

    public function __construct(SynapseEntry $server, string $ip, int $port) {
        $this->synapse = $server;
        $this->registerPackets();
        $this->client = new SynapseClient(Server::getInstance()->getLogger(), $port, $ip);
    }
    public function registerPackets(){

    }
}