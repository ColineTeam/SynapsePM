<?php //https://github.com/iTXTech/SynapseAPI/blob/master/src/main/java/org/itxtech/synapseapi/network/SynapseInterface.java
namespace synapsepm\network;

use pocketmine\Server;
use pocketmine\snooze\SleeperNotifier;
use synapsepm\network\protocol\spp\{HeartbeatPacket, ConnectPacket, DisconnectPacket, SynapseInfo, SynapseDataPacket};
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

        $this->getSynapse()->getSynapse()->getServer()->getTickSleeper()->addNotifier($notifier = new \pocketmine\snooze\SleeperNotifier(), function (): void {
            $this->client->connect();
        });
        $this->client = new SynapseClient(Server::getInstance()->getLogger(), $port, $ip, $notifier);
        $this->client->start();
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
    public function registerPacket($id, $class){
        $this->packetPool[$id] = new $class;
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
    public function putPacket(SynapseDataPacket $pk){
        if($pk->isEncoded){
            $pk->encode();
        }
        $this->client->pushMainToThreadPacket($pk);
    }
    public function isConnected(){
        return $this->client->isConnected();
    }
    public function process(){
//        $pk = $this->client->readMainToThreadPacket();
//        while ($pk !== null){
//            $this->handlePacket($pk);
//            $pk = $this->client->readMainToThreadPacket();
//        }

        $this->connected = $this->client->isConnected();
        if($this->connected && $this->client->isNeedAuth()){
            $this->synapse->connect();
            $this->client->setNeedAuth(false);
        }
    }
    public function handlePacket(SynapseDataPacket $pk){
        if($pk !== null){
            $pk->decode();
            $this->synapse->handleDataPacket($pk);
        }
    }
    public function registerPackets(){
        $this->packetPool = new \SplFixedArray(256);

        $this->registerPacket(SynapseInfo::HEARTBEAT_PACKET, HeartbeatPacket::class);
        $this->registerPacket(SynapseInfo::CONNECT_PACKET, ConnectPacket::class);
        $this->registerPacket(SynapseInfo::DISCONNECT_PACKET, DisconnectPacket::class);
//        $this->registerPacket(SynapseInfo::REDIRECT_PACKET, RedirectPacket::class);
//        $this->registerPacket(SynapseInfo::PLAYER_LOGIN_PACKET, PlayerLoginPacket::class);
//        $this->registerPacket(SynapseInfo::PLAYER_LOGOUT_PACKET, PlayerLogoutPacket::class);
//        $this->registerPacket(SynapseInfo::INFORMATION_PACKET, InformationPacket::class);
//        $this->registerPacket(SynapseInfo::TRANSFER_PACKET, TransferPacket::class);
//        $this->registerPacket(SynapseInfo::BROADCAST_PACKET, BroadcastPacket::class);
//        $this->registerPacket(SynapseInfo::FAST_PLAYER_LIST_PACKET, FastPlayerListPacket::class);
    }
}