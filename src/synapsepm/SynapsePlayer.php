<?php
namespace synapsepm;


use pocketmine\network\SourceInterface;
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
    private static function getClientFriendlyGamemode(int $gamemode): int{
        if($gamemode == \pocketmine\Player::SPECTATOR){
            return \pocketmine\Player::CREATIVE;
        }
        return $gamemode;
    }
    public function handleLoginPacket(PlayerLoginPacket $packet){
        if($this->isSynapseLogin){
//            parent::handleDataPacket(SynapseAPI::getInstance()->)
        }
    }

}