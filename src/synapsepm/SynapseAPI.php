<?php //https://github.com/iTXTech/SynapseAPI/blob/master/src/main/java/org/itxtech/synapseapi/SynapseAPI.java
namespace synapsepm;

use pocketmine\network\mcpe\protocol\PacketPool;
use pocketmine\plugin\PluginBase;
use synapsepm\messaging\StandardMessenger;

class SynapseAPI extends PluginBase {
    public static $enable = true;
    private static $instance;
    private $autoConnect = true;
    private $loadingScreen = false;
    private $synapseEntries = [];
    private $autoCompress = false; //Compress in Nukkit, not Nemisys
    private $messenger = [];

    public static function getInstance() : SynapseAPI{
        return SynapseAPI::$instance;
    }
    public function isAutoConnect(): bool{
        return $this->autoConnect;
    }
    public function onLoad() {
        SynapseAPI::$instance = $this;
    }
    public function onEnable() {
       $this->messenger = new StandardMessenger();
       $this->loadEntries();
    }
    public function isUseLoadingScreen(){
        return $this->loadingScreen;
    }
    public function isAutoCompress(){
        return $this->autoCompress;
    }
    public function getSynapseEntries(){
        $this->synapseEntries;
    }
    public function addSynapseAPI(SynapseEntry $entry){
        $this->synapseEntries[$entry->getHash()] = $entry;
    }
    public function getSynapseEntry(string $hash){
        return $this->synapseEntries[$hash];
    }
    public function shutdownAll(){
        foreach ($this->synapseEntries as $entry){
            $entry->shutdown();
        }
    }
    public function onDisable() {
       $this->shutdownAll();
    }
    public function getPacket($buffer){
        $pid = ord($buffer{0});
        if ($pid === 0xFF) {
            $pid = 0xFE;
        }
        if (($data = PacketPool::getPacketById($pid)) === null) {
            return null;
        }
        $data->setBuffer($buffer, 1);
        return $data;
    }

    public function loadEntries(){
        $this->saveDefaultConfig();
        $enable = $this->getConfig()->get('enable', true);
        $this->autoCompress = $this->getConfig()->get('autoCompress', true);
        if(!$enable){
            $this->getLogger()->info("The SynapseAPI is not be enabled!");
        }else{
            if($this->getConfig()->get('disable-rak')){
                $this->getServer()->getPluginManager()->registerEvents(new DisableRakListener(), $this);
            }
            $entries = $this->getConfig()->get('synapses');
            foreach (@$entries as $entry){
                $this->autoConnect = $this->getConfig()->get("autoConnect", true);
                if($this->autoConnect){
                    if($entry['enabled']){
                        $this->addSynapseAPI(new SynapseEntry($this, $entry['ip'], $entry['port'], $entry['is-main'], $entry['password'], $entry['description']));
                    }
                }
            }
        }
    }
    public function getMessenger(){
        return $this->messenger;
    }
}