<?php //https://github.com/iTXTech/SynapseAPI/blob/master/src/main/java/org/itxtech/synapseapi/network/SynapseInterface.java
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
    public function getPacket($pid, $buffer){
        $class = $this->packetPool[$pid];
        if ($class !== null) {
            $pk = clone $class;
            $pk->setBuffer($buffer, 1);
            return $pk;
        }
        return null;
    }
    /* @var $packet - SynapseDataPacket*/
    public function registerPacket($id, $packet){
        $this->packetPool[$id] = $packet;
    }
    public function getSynapse(){
        return $this->synapse;
    }
    public function reconnect(){
        $this->client->reconnect();
    }
    public function getPutPacketThread(){
        return $this->putPacketThread;
    }
    public function putPacket($pk){

    }
    public function isConnected(){
        return $this->connected;
    }
    public function process(){
        $pk = $this->client->readMainToThreadPacket();
        while ($pk !== null){
            $this->handlePacket($pk);
            $pk = $this->client->readMainToThreadPacket();
        }

        $this->connected = $this->client->isConnected();
        if($this->connected && $this->client->isNeedAuth()){
            $this->synapse->connect();
            $this->client->setNeedAuth(false);
        }
    }
    public function handlePacket($pk){
        if($pk !== null){
            $pk->decode();
            $this->synapse->handleDataPacket($pk);
        }
    }
    public function registerPackets(){
        $this->packetPool = new \SplFixedArray(256);

        //packets
    }
}