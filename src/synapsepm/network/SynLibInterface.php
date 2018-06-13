<?php //complete https://github.com/iTXTech/SynapseAPI/blob/master/src/main/java/org/itxtech/synapseapi/network/SynLibInterface.java
namespace synapsepm\network;

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\SourceInterface;
use synapsepm\network\protocol\spp\RedirectPacket;
use synapsepm\network\SynapseInterface;
use pocketmine\Player;

class SynLibInterface implements SourceInterface {
    private $synapseInterface;

    public function __construct(SynapseInterface $synapseInterface){
        $this->synapseInterface = $synapseInterface;
    }
    public function getNetworkLatency(Player $player) {
       return 0;
    }
    public function emergencyShutdown() {
    }
    public function setName(string $name) {
    }
    public function process(): void {
//        return null;
    }
    public function close(Player $player, string $reason = "unknown reason") {
    }
    public function putPacket(Player $player, DataPacket $packet, bool $needACK = \false, bool $immediate = \true) {
        if($player->isClosed()){
            $pk = new RedirectPacket();
            $pk->uuid = $player->getUniqueId();
            $pk->direct = $immediate;
            if (!$packet->isEncoded) {
                $packet->encode();
                $packet->isEncoded = true;
            }
            $this->synapseInterface->putPacket($pk);
        }
//        $this->synapseInterface->getPutPacketThread()->addMainToThread($player, $packet, $needACK, $immediate);
    }
    public function shutdown() {
    }
    public function start(){}

}