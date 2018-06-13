<?php //https://github.com/iTXTech/SynapseAPI/blob/master/src/main/java/org/itxtech/synapseapi/SynapseEntry.java
namespace synapsepm;

use pocketmine\event\player\PlayerCreationEvent;
use pocketmine\scheduler\Task;
use pocketmine\scheduler\TaskHandler;
use pocketmine\Server;
use pocketmine\utils\UUID;
use synapsepm\event\player\SynapsePlayerCreationEvent;
use synapsepm\network\protocol\spp\{InformationPacket, SynapseDataPacket, BroadcastPacket, ConnectPacket, DisconnectPacket, HeartbeatPacket, SynapseInfo};
use synapsepm\network\SynapseInterface;
use synapsepm\network\SynLibInterface;
use synapsepm\SynapseAPI;
use \pocketmine\snooze\SleeperNotifier;
use synapsepm\SynapsePlayer;


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
        $sleeper = $this->getSynapse()->getServer()->getTickSleeper();

        $sleeper->addNotifier($notifier = new \pocketmine\snooze\SleeperNotifier(), function (): void {
           $this->threadTick();
        });

        $this->thread = new AsyncTicker($notifier);
        $this->thread->start();

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
//                time_sleep_until(time() + 10);
            } catch (Exception $e) {
                //ignore
            }
        }
        $this->getSynapse()->getLogger()->debug('shutdown '.$this->getHash());
//        $this->thread->quit();
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
    private function getLogger(){
        return $this->getSynapse()->getLogger();
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
        if (!$this->synapseInterface->isConnected()) return;
        $time = microtime(true);
        if ($time - $this->lastUpdate >= 5) {//Heartbeat!
            $this->lastUpdate = $time;
            $this->getSynapse()->getServer()->getLogger()->warning("HeartbeatPacket! ");
            $pk = new HeartbeatPacket();
            $pk->tps = $this->getSynapse()->getServer()->getTicksPerSecondAverage();
            $pk->load = $this->getSynapse()->getServer()->getTickUsageAverage();
            $pk->upTime = (microtime(true) - \pocketmine\START_TIME);
            $this->sendDataPacket($pk);
        }
        $finalTime = microtime(true);
        $usedTime = $finalTime - $time;
        if ((($finalTime) - $this->lastUpdate) >= 30 && $this->synapseInterface->isConnected()) { //30 seconds timeout
            $this->synapseInterface->reconnect();
        }

    }

    public function removePlayer($player) {
        // if($player instanceof SynapsePlayer) $uuid = $player->getUniqueId();
        if (isset($this->players[$uuid = $player->getUniqueId()->toBinary()])) {
            unset($this->players[$uuid]);
        }
    }

    public function handleDataPacket(SynapseDataPacket $pk) {
        switch ($pk->pid()) {
            case SynapseInfo::DISCONNECT_PACKET:
                /** @var DisconnectPacket $pk */
                $this->verified = false;
                switch ($pk->type) {
                    case DisconnectPacket::TYPE_GENERIC:
                        $this->getLogger()->notice('Synapse Client has disconnected due to ' . $pk->message);
                        $this->synapseInterface->reconnect();
                        break;
                    case DisconnectPacket::TYPE_WRONG_PROTOCOL:
                        $this->getLogger()->error($pk->message);
                        break;
                }
                break;
            case SynapseInfo::INFORMATION_PACKET:
                /** @var InformationPacket $pk */
                switch ($pk->type) {
                    case InformationPacket::TYPE_LOGIN:
                        if ($pk->message === InformationPacket::INFO_LOGIN_SUCCESS) {
                            $this->getSynapse()->getLogger()->info('Login success to ' . $this->serverIp . ':' . $this->port);
                            $this->verified = true;
                        } elseif ($pk->message === InformationPacket::INFO_LOGIN_FAILED) {
                            $this->getSynapse()->getLogger()->info('Login failed to ' . $this->serverIp . ':' . $this->port);
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
            case SynapseInfo::PLAYER_LOGIN_PACKET:
                $this->getSynapse()->getLogger()->info('PlayerLoginPacket');
//                var_dump($pk);
                /** @var PlayerLoginPacket $pk */
                $ev = new PlayerCreationEvent($this->synLibInterface, SynapsePlayer::class, SynapsePlayer::class, $pk->address, $pk->port);
                $this->getSynapse()->getServer()->getPluginManager()->callEvent($ev);
                $ev = new SynapsePlayerCreationEvent($this->synLibInterface, SynapsePlayer::class, SynapsePlayer::class, $pk->address, $pk->port);
                $this->getSynapse()->getServer()->getPluginManager()->callEvent($ev);

                $class = $ev->getPlayerClass();
                /** @var SynapsePlayer $player */
                $player = new $class($this->synLibInterface, $ev->getAddress(), $ev->getPort());

//                $player->setUniqueId($pk->uuid);
                $this->getSynapse()->getServer()->addPlayer($player);
                $this->players[$pk->uuid->toBinary()] = $player;
                $player->handleLoginPacket($pk);
                break;
            case SynapseInfo::REDIRECT_PACKET:
                /** @var RedirectPacket $pk */
                if (isset($this->players[$uuid = $pk->uuid->toBinary()])) {
                    $innerPacket = SynapseAPI::getInstance()->getPacket($pk->mcpeBuffer);
                    if ($innerPacket !== null) {
                        $this->players[$uuid]->handleDataPacket($innerPacket);
                    }
                }
                break;
            case SynapseInfo::PLAYER_LOGOUT_PACKET:
                $this->getSynapse()->getLogger()->info('PLAYER_LOGOUT_PACKET');

                /** @var PlayerLogoutPacket $pk */
                if (isset($this->players[$uuid = $pk->uuid->toBinary()])) {
                    $this->players[$uuid]->close('', $pk->reason, false);
                    $this->removePlayer($this->players[$uuid]);
                }
                break;
            default:
                $this->getSynapse()->getLogger()->info('not found pk '.var_dump($pk));
                break;
        }
    }
}

class AsyncTicker extends \pocketmine\Thread {
    public $notifier, $lastWarning = 0;

    public function __construct(SleeperNotifier $notifier) {
        $this->notifier = $notifier;
    }

    public function run() {
        $this->registerClassLoader();
        while (true) {
            $startTime = microtime(true);
            $this->notifier->wakeupSleeper();
            $tickUseTime = microtime(true) - $startTime;
            if ($this->tickUseTime < 10) {
                sleep(2 - $tickUseTime);
            } elseif (microtime(true) - $this->lastWarning >= 5000) {
                print_r("SynapseEntry<???> Async Thread is overloading! TPS: {indev} tickUseTime: " . $this->tickUseTime);
                $this->lastWarning = microtime(true);
            }
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
