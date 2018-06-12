<?php //https://github.com/iTXTech/SynapseAPI/blob/master/src/main/java/org/itxtech/synapseapi/network/synlib/SynapseClient.java
namespace synapsepm\network\synlib;

use pocketmine\Server;
use pocketmine\Thread;
use synapsepm\network\protocol\spp\SynapseDataPacket;
use pocketmine\snooze\SleeperNotifier;

class SynapseClient extends Thread {
    const VERSION = "0.3.0";
    public $needReconnect = false;
    protected $externalQueue = [], $internalQueue = [];
    private $logger, $interfaz, $port;
    private $shutdown = false;
    private $needAuth = true;
    private $connected = false;
    private $clientGroup;
    private $session;

    public function __construct($logger, int $port, string $interfaz, SleeperNotifier $notifier) {
        $this->logger = $logger;
        $this->interfaz = $interfaz;
        $this->port = $port;
        if ($port < 1 || $port > 65536) {
            throw new \Exception('Invalid port range'); //TODO: change to IllegalArgumentException
        }
        $this->shutdown = false;
        $this->notifier = $notifier;
    }

    public function reconnect() {
        $this->needReconnect = true;
    }

    public function isNeedAuth(): bool {
        return $this->needAuth;
    }

    public function setNeedAuth(bool $needAuth) {
        $this->needAuth = $needAuth;
    }

    public function isConnected() {
        return $this->connected;
    }

    public function setConnected(bool $connected) {
        $this->connected = $connected;
    }

    public function getExternalQueue() {
        return $this->externalQueue;
    }

    public function getInternalQueue() {
        return $this->internalQueue;
    }

    public function isShutdown(): bool {
        return $this->shutdown;
    }

    public function shutdown() {
        $this->shutdown = true;
    }

    public function getPort() {
        return $this->port;
    }

    public function getInterface(): string {
        return $this->interfaz;
    }

    public function getLogger() {
        return $this->logger;
    }

    public function quit() {
        $this->shutdown();
    }

    public function pushMainToThreadPacket(SynapseDataPacket $pk) {
        if(!$pk->isEncoded){
            $pk->encode();
        }
        $this->internalQueue[] = $pk->buffer; //не понятно this.internalQueue.offer(data); добовляет в начало или конец списка
    }

    public function readMainToThreadPacket() {
        if (count($this->internalQueue) == 0) return null;
        $arr = array_values(get_object_vars($this->internalQueue));
        $elem = count($arr) -1;
        unset($this->internalQueue[$elem]);
        for ($i = 0; $i <= $elem+1; $i++) {
            $this->internalQueue[$i] = $this->internalQueue[$i+1];
        }
        return $arr[$elem];
        //return array_shift($this->internalQueue);

    }

    public function pushThreadToMainPacket($data) {
        $this->externalQueue[] = $data;
    }

    public function readThreadToMainPacket() {
        if (count($this->externalQueue) == 0) return null;
        $arr = array_values(get_object_vars($this->externalQueue));
        $elem = count($arr) -1;
        unset($this->externalQueue[$elem]);
        for ($i = 0; $i <= $elem+1; $i++) {
            $this->externalQueue[$i] = $this->externalQueue[$i+1];
        }
        return $arr[$elem];
    }

    public function getSession() {
        return $this->session;
    }

    public function run() {
        $this->registerClassLoader();
        register_shutdown_function([$this, 'shutdownHandler']);
        try {
            $this->connect();
            $this->notifier->wakeupSleeper();
        } catch (\Exception $e) {
            Server::getInstance()->getLogger()->logException($e);
        }
    }

    public function connect() {
        $socket = new SynapseSocket($this->getLogger(), $this->port, $this->interfaz);
        new ServerConnection($this, $socket);
    }

    public function getClientGroup() {
        return $this->clientGroup;
    }

    public function shutdownHandler() {
        if ($this->shutdown !== true) {
            $this->getLogger()->emergency('SynLib crashed!');
        }
    }

}