<?php
namespace synapsepm;


use pocketmine\network\SourceInterface;
use synapsepm\event\player\SynapsePlayerConnectEvent;
use synapsepm\network\protocol\spp\PlayerLoginPacket;
use synapsepm\network\protocol\spp\SynapseInfo;

class SynapsePlayer extends \pocketmine\Player {
    private static $handlePlayerDataPacketTimings = [];
    public $isSynapseLogin = false;
    protected $synapseEntry;
    private $isFirstTimeLogin = false;
    private $synapseSlowLoginUntil = 0;

    public function __construct(SourceInterface $interfaz, string $ip, int $port) {
        parent::__construct($interfaz, $ip, $port);
        $this->isSynapseLogin = $this->synapseEntry != null;
    }
    public static function getClientFriendlyGamemode(int $gamemode) : int{
    $gamemode &= 0x03;
    if($gamemode === Player::SPECTATOR){
        return Player::CREATIVE;
    }

    return $gamemode;
}
    public function handleLoginPacket(PlayerLoginPacket $packet){
        if(!$this->isSynapseLogin){
            parent::handleDataPacket(SynapseAPI::getInstance()->getPacket($packet->cachedLoginPacket));
        }
        $this->isFirstTimeLogin = $packet->isFirstTime;
        $this->server->getPluginManager()->callEvent($ev = new SynapsePlayerConnectEvent($this, $this->isFirstTimeLogin));
        if(!$ev->isCancelled()){
            $pk = SynapseAPI::getInstance()->getPacket($packet->cachedLoginPacket);
                var_dump($pk);
            /** @var PlayerLoginPacket $pk */
//            $pk->offset = 3;
            $pk->decode();
            $this->handleLogin($pk);
        }
    }

}