<?php //https://github.com/iTXTech/SynapseAPI/blob/master/src/main/java/org/itxtech/synapseapi/SynapseEntry.java
namespace synapsepm;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
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

    public function __construct(SynapseAPI $synapse, string $serverIp, int $port, bool $isMainServer, string $password, string $serverDescription) {
        $this->synapse = $synapse;
        $this->serverIp = $serverIp;
        $this->port = $port;
        $this->isMainServer = $isMainServer;
        $this->password = $password;
        if(strlen($password) >= 16){
            $this->synapse->getLogger()->warning("You must use a 16 bit length key!");
            $this->synapse->getLogger()->warning("This SynapseAPI Entry will not be enabled!");
            $enable = false;
        }
        $this->serverDescription = $serverDescription;

        $this->synapseInterface = new SynapseInterface($this, $this->serverIp, $this->port);
        $this->synLibInterface = new SynLibInterface($synapse);

        $this->lastUpdate = microtime(true);
        $this->lastRecvInfo = microtime(true);
//        $this->getSynapse()->getServer()->getScheduler()->scheduleRepeatingTask(SynapseAPI::getInstance(), new);

    }
    public function getSynapse(): SynapseAPI{
        return $this->synapse;
    }


    public function threadTick(){
        $this->synapseInterface;
    }
}
class AsyncTicker extends AsyncTask {
    public $tickUseTime;
    public $lastWarning = 0;
    /* @var SynapseEntry */
    public $entry;
    public function __construct(SynapseEntry $entry) {
        $this->entry = $entry;
    }

    public function onRun() {
        $startTime = microtime(true);
        while (Server::getInstance()->isRunning()){
            $this->entry->threadTick();
            $this->tickUseTime = microtime(true) - $startTime;
            if($this->tickUseTime < 10){
                @time_sleep_until(10 - $this->tickUseTime);
            }elseif (microtime(true) - $this->lastWarning >= 5000){
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
