<?php ////https://github.com/iTXTech/SynapseAPI/blob/master/src/main/java/org/itxtech/synapseapi/network/synlib/Session.java
//namespace synapsepm\network\synlib;
//
//
//use pocketmine\Server;
//
//class Session {
//
//    public $channel;
//    private $ip;
//    private $port;
//    private $client;
//    private $lastCheck;
//    private $connected;
//    private $tickUseTime = 0;
//
//    public function __construct(SynapseClient $client) {
//        $this->client = $client;
//        $this->connected = true;
//        $this->lastCheck = microtime(true);
//    }
//    public function updateAddress($ip, $port){
//        $this->ip = $ip;
//        $this->port = $port;
//    }
//    public function setConnected(bool $conected){
//        $this->connected = $conected;
//    }
//    public function run(){
//        $this->tickProcessor();
//    }
//    private function tickProcessor(){
//        while (!$this->client->isShutdown()){
//            $start = microtime(true);
//            try{
//                $this->tick();
//            }catch (\Exception $e){
//               Server::getInstance()->getLogger()->logException($e);
//            }
//            $time = microtime(true) - $start
//            $this->tickUseTime = $time;
//            if($time < 0.1){
//                echo ($time + 0.01 - ($time - $start)).PHP_EOL;
//                @time_sleep_until($time + 0.01 - ($time - $start));
//            }
//        }
//        if ($this->connected){
//            //$this->client->getc
//        }
//    }
//    private function tick() {
//        $this->update();
//        if (($packets = $this->readPackets()) !== null) {
//            foreach ($packets as $packet) {
//                $this->server->pushThreadToMainPacket($packet);
//            }
//        }
//        while (($packet = $this->server->readMainToThreadPacket()) !== null && strlen($packet) !== 0) {
//            $this->writePacket($packet);
//        }
//    }
//    public function getHash() : string {
//        return $this->ip . ':' . $this->port;
//    }
//
//    public function getIp() : string {
//        return $this->ip;
//    }
//
//    public function getPort() : int {
//        return $this->port;
//    }
//    public function update():bool{
//        if($this->client->needReconnect && $this->connected){
//            $this->connected = false;
//            $this->client->needReconnect = false;
//        }
//        if(!$this->connected && $this->client->isShutdown()){
//            if(($time = microtime()) - $this->lastCheck >= 30){
//                $this->client->co
//            }
//        }
//    }
//
//}