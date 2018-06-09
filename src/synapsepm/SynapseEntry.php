<?php //https://github.com/iTXTech/SynapseAPI/blob/master/src/main/java/org/itxtech/synapseapi/SynapseEntry.java
namespace synapsepm;

use pocketmine\scheduler\AsyncTask;
use pocketmine\scheduler\PluginTask;
use pocketmine\scheduler\TaskHandler;
use pocketmine\Server;
use synapseapi\network\protocol\spp\SynapseDataPacket;
use synapsepm\network\SynapseInterface;
use synapsepm\network\SynLibInterface;
use synapsepm\SynapseAPI;

class SynapseEntry {
    /* @var SynapseAPI $synapse */
    private $synapse;
    private $enable;
    private $serverIp;
    private $port;
    private $isMainServer;
    private $password;
    /* @var SynapseInterface $synapseInterface */
    private $synapseInterface;
    private $verified;
    private $lastUpdate, $lastRecvInfo;
    private $players = [];
    /* @var SynLibInterface $synLibInterface */
    private $synLibInterface;
    /* @var ClientData $clientData */
    private $clientData;
    private $serverDescription;
    private $playerLoginQueue = [], $playerLogoutQueue = [], $redirectPacketQueue = [];

    public function __construct(SynapseAPI $synapse, string $serverIp, int $port, bool $isMainServer, string $password, string $serverDescription) {
        $this->synapse = $synapse;
        $this->serverIp = $serverIp;
        $this->port = $port;
        $this->isMainServer = $isMainServer;
        $this->password = $password;
        if (strlen($password) >= 16) {
            $this->synapse->getLogger()->warning("You must use a 16 bit length key!");
            $this->synapse->getLogger()->warning("This SynapseAPI Entry will not be enabled!");
            $enable = false;
        }
        $this->serverDescription = $serverDescription;

        $this->synapseInterface = new SynapseInterface($this, $this->serverIp, $this->port);
        $this->synLibInterface = new SynLibInterface($synapse);

        $this->lastUpdate = microtime(true);
        $this->lastRecvInfo = microtime(true);
        $this->getSynapse()->getServer()->getScheduler()->scheduleRepeatingTask(new AsyncTicker($this), 1);
        $this->getSynapse()->getServer()->getScheduler()->scheduleAsyncTask(new Ticker($this));
    }

    public function getRandomString($lenght) {
        return base_convert(sha1(uniqid(mt_rand(), true)), 16, $lenght);
    }

    public function getSynapse(): SynapseAPI {
        return $this->synapse;
    }

    public function isEnable() {
        return $this->enable;
    }

    public function getClientData() {
        $this->clientData;
    }

    public function getSynapseInterface() {
        return $this->synapseInterface;
    }

    public function shutdown() {
        if ($this->verified) {
            $pk = new DisconnectPacket();
            $pk->type = DisconnectPacket::TYPE_GENERIC;
            $pk->message = "Server closed";
            $this->sendDataPacket($pk);
            $this->getSynapse()->getLogger()->debug("Synapse client has disconnected from Synapse synapse");
            try {
                time_sleep_until(time() + 100);
            } catch (Exception $e) {
                //ignore
            }
        }
        if ($this->synapseInterface != null) $this->synapseInterface->shutdown();
    }

    public function getServerDescription() {
        return $this->serverDescription;
    }

    public function setServerDescription($serverDescription) {
        $this->serverDescription = $serverDescription;
    }

    public function sendDataPacket(SynapseDataPacket $pk) {
        $this->synapseInterface->putPacket($pk);
    }

    public function setPassword($password) {
        $this->password = $password;
    }

    public function getServerIp() {
        return $this->serverIp;
    }

    public function setServerIp($serverIp) {
        $this->serverIp = $serverIp;
    }

    public function getPort(): int {
        return $this->port;
    }

    public function setPort($port) {
        $this->port = $port;
    }
    public function broadcastPacket($players, SynapseDataPacket $packet, bool $direct){
        $packet->encode();
        $broadcastPacket = new BroadcastPacket();
        $broadcastPacket->direct = $direct;
        $broadcastPacket->payload = $packet->getBuffer();
        $broadcastPacket->entries = array();
        for ($players as $player) {
            $broadcastPacket->entries[] = $player->getUniqueId();
        }
        $this->sendDataPacket($broadcastPacket);
    }
    public function isMainServer() {
        return $this->isMainServer;
    }

    public function setMainServer(bool $mainServer) {
        $this->isMainServer = $mainServer;
    }

    public function threadTick() {
        $this->synapseInterface;
    }
    
     public function getHash() {
        return $this->serverIp . ":" . $this->port;
    }
    
     public function connect() {
        $this->getSynapse()->getLogger()->notice("Connecting " . $this.getHash());
        $this->verified = false;
        $pk = new ConnectPacket();
        $pk->password = $this->password;
        $pk->isMainServer = $this->isMainServer();
        $pk->description = $this->serverDescription;
        $pk->maxPlayers = $this->getSynapse()->getServer()->getMaxPlayers();
        $pk->protocol = SynapseInfo::CURRENT_PROTOCOL;
        $this->sendDataPacket($pk);
    }
}

class AsyncTicker extends PluginTask {
    public $tickUseTime;
    public $lastWarning = 0;
    /* @var SynapseEntry */
    public $entry;

    public function __construct(SynapseEntry $entry) {
        $this->entry = $entry;
    }

    public function onRun(int $currentTick) {
        $startTime = microtime(true);
        while (Server::getInstance()->isRunning()) {
            $this->entry->threadTick();
            $this->tickUseTime = microtime(true) - $startTime;
            if ($this->tickUseTime < 10) {
                @time_sleep_until(10 - $this->tickUseTime);
            } elseif (microtime(true) - $this->lastWarning >= 5000) {
                Server::getInstance()->getLogger()->warning("SynapseEntry<???> Async Thread is overloading! TPS: {indev} tickUseTime: " . $this->tickUseTime);
                $this->lastWarning = microtime(true);
            }
            $startTime = microtime(true);
        }
    }
}

class Ticker extends AsyncTask {
    public $tickUseTime;
    public $lastWarning = 0;

    public function __construct(SynapseEntry $entry) {
        $this->entry = $entry;
    }

    public function onRun() {

    }
}
