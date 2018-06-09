<?php
namespace synapsepm;

use pocketmine\plugin\PluginBase;
use synapsepm\messaging\StandardMessenger;

class SynapseAPI extends PluginBase {
    public static $enable = true;
    private static $instance;
    private $autoConnect = true;
    private $loadingScreen = false;
    private $autoCompress = true; //Compress in Nukkit, not Nemisys

    public function getInstance() : SynapseAPI{
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
            $entries = $this->getConfig()['entries'];
            foreach ($entries as $entry){

            }
        }
    }
}