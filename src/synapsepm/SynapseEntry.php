<?php //https://github.com/iTXTech/SynapseAPI/blob/master/src/main/java/org/itxtech/synapseapi/SynapseEntry.java
namespace synapsepm;

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

}