<?php //https://github.com/iTXTech/SynapseAPI/blob/master/src/main/java/org/itxtech/synapseapi/SynapseEntry.java
namespace synapsepm;

use pocketmine\scheduler\Task;
use pocketmine\scheduler\TaskHandler;
use pocketmine\Server;
use pocketmine\utils\UUID;
use synapseapi\network\protocol\spp\SynapseDataPacket;
use synapsepm\network\protocol\spp\BroadcastPacket;
use synapsepm\network\protocol\spp\ConnectPacket;
use synapsepm\network\protocol\spp\DisconnectPacket;
use synapsepm\network\protocol\spp\HeartbeatPacket;
use synapsepm\network\protocol\spp\SynapseInfo;
use synapsepm\network\SynapseInterface;
use synapsepm\network\SynLibInterface;
use synapsepm\SynapseAPI;
use \pocketmine\snooze\SleeperNotifier;


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
        if (mb_strlen($password) > 16) {
            $this->synapse->getLogger()->warning("You must use a 16 bit length key!");
            $this->synapse->getLogger()->warning("This SynapseAPI Entry will not be enabled!");
            $this->enable = false;
        }
        $this->serverDescription = $serverDescription;

        $this->synapseInterface = new SynapseInterface($this, $this->serverIp, $this->port);
        $this->synLibInterface = new SynLibInterface($this->synapseInterface);

        $this->lastUpdate = microtime(true);
        $this->lastRecvInfo = microtime(true);

        $notifier = new SleeperNotifier();
        $this->getSynapse()->getServer()->getTickSleeper()->addNotifier($notifier, function() : void{
            $this->threadTick();
        });
        $thread = new AsyncTicker($notifier);
        $thread->start();
        $this->getSynapse()->getScheduler()->scheduleRepeatingTask(new Ticker($this), 1);
    }

    public function getRandomString(int $lenght) {
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

    public function setPort(int $port) {
        $this->port = $port;
    }

    public function broadcastPacket($players, SynapseDataPacket $packet, bool $direct = false) {
        $packet->encode();
        $broadcastPacket = new BroadcastPacket();
        $broadcastPacket->direct = $direct;
        $broadcastPacket->payload = $packet->getBuffer();
        $broadcastPacket->entries = [];
        foreach ($players as $player) {
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

    public function getHash() {
        return $this->serverIp . ":" . $this->port;
    }

    public function connect() {
        $this->getSynapse()->getLogger()->notice("Connecting " . $this->getHash());
        $this->verified = false;
        $pk = new ConnectPacket();
        $pk->password = $this->password;
        $pk->isMainServer = $this->isMainServer();
        $pk->description = $this->serverDescription;
        $pk->maxPlayers = $this->getSynapse()->getServer()->getMaxPlayers();
        $pk->protocol = SynapseInfo::CURRENT_PROTOCOL;
        $this->sendDataPacket($pk);

    }

    public function threadTick() {
        $this->synapseInterface->process();
        if (!$this->synapseInterface->isConnected() || $this->verified) return;
        $time = microtime(true);
        if ($time - $this->lastUpdate >= 5000) {//Heartbeat!
            $this->lastUpdate = $time;
            $pk = new HeartbeatPacket();
            $pk->tps = $this->getSynapse()->getServer()->getTicksPerSecondAverage();
            $pk->load = $this->getSynapse()->getServer()->getTickUsageAverage();
            $pk->upTime = (microtime(true) - \pocketmine\START_TIME) / 1000;
            $this->sendDataPacket($pk);
        }
        $finalTime = microtime(true);
        $usedTime = $finalTime - $time;
        $this->getSynapse()->getServer()->getLogger()->warning("time ConnectPacket " . $usedTime);
        if ((($finalTime) - $this->lastUpdate) >= 30000 && $this->synapseInterface->isConnected()) { //30 seconds timeout
            $this->synapseInterface->reconnect();
        }

    }

    public function removePlayer($player) {
        // if($player instanceof SynapsePlayer) $uuid = $player->getUniqueId();
        if (isset($this->players[$uuid = $player->getUniqueId()->toBinary()])) {
            unset($this->players[$uuid]);
        }
        //TODO: разобрать зачем эта функция и где она используеться
    }

    public function handleDataPacket(SynapseDataPacket $pk) {
        switch ($pk->pid()) {
            case Info::DISCONNECT_PACKET:
                /** @var DisconnectPacket $pk */
                $this->verified = false;
                switch ($pk->type) {
                    case DisconnectPacket::TYPE_GENERIC:
                        $this->getLogger()->notice('Synapse Client has disconnected due to ' . $pk->message);
                        $this->interface->reconnect();
                        break;
                    case DisconnectPacket::TYPE_WRONG_PROTOCOL:
                        $this->getLogger()->error($pk->message);
                        break;
                }
                break;
            case Info::INFORMATION_PACKET:
                /** @var InformationPacket $pk */
                switch ($pk->type) {
                    case InformationPacket::TYPE_LOGIN:
                        if ($pk->message === InformationPacket::INFO_LOGIN_SUCCESS) {
                            $this->logger->info('Login success to ' . $this->serverIp . ':' . $this->port);
                            $this->verified = true;
                        } elseif ($pk->message === InformationPacket::INFO_LOGIN_FAILED) {
                            $this->logger->info('Login failed to ' . $this->serverIp . ':' . $this->port);
                        }
                        break;
                    case InformationPacket::TYPE_CLIENT_DATA:
                        $this->clientData = json_decode($pk->message, true)['clientList'];
                        $this->lastRecvInfo = microtime();
                        break;
//                    case InformationPacket::TYPE_PLUGIN_MESSAGE:
//                        $this->server->getPluginManager()->callEvent(new SynapsePluginMessageReceiveEvent($this, $pk->message));
//                        break;
                }
                break;
        }
    }
}

class AsyncTicker extends  \Thread {
    public $tickUseTime;
    public $lastWarning = 0;
    /* @var SynapseEntry */
    public $notifier;

    public function __construct(SleeperNotifier $notifier) {
        $this->notifier = $notifier;
    }

    public function run() {
        $startTime = microtime(true);
        while (true) {
//            $this->entry->threadTick();
            $this->tickUseTime = microtime(true) - $startTime;
            if ($this->tickUseTime < 10) {
                @sleep(10 - $this->tickUseTime);
            } elseif (microtime(true) - $this->lastWarning >= 5000) {
                print_r("SynapseEntry<???> Async Thread is overloading! TPS: {indev} tickUseTime: " . $this->tickUseTime);
                $this->lastWarning = microtime(true);
            }
            $startTime = microtime(true);
            $this->notifier->wakeupSleeper();
        }
    }
}

class Ticker extends Task {
    public $tickUseTime;
    public $lastWarning = 0;

    public function __construct(SynapseEntry $entry) {
        //$this->entry = $entry;
    }

    public function onRun(int $currentTick) {

    }

}
