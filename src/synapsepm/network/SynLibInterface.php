<?php //complete https://github.com/iTXTech/SynapseAPI/blob/master/src/main/java/org/itxtech/synapseapi/network/SynLibInterface.java
namespace synapsepm\network;

use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\SourceInterface;
use synapsepm\network\protocol\spp\RedirectPacket;
use synapsepm\network\protocol\spp\SynapseInfo;
use synapsepm\network\SynapseInterface;
use pocketmine\Player;
use synapsepm\SynapseAPI;

class SynLibInterface implements SourceInterface {
    private $synapseInterface;

    public function __construct(SynapseInterface $synapseInterface) {
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
        if (!$player->isClosed() && ($packet instanceof \pocketmine\network\mcpe\protocol\LoginPacket) == false) {
            var_dump($packet);
            $pk = new RedirectPacket();
            $pk->uuid = $player->getUniqueId();
            $pk->direct = $immediate;
            if (!$packet->isEncoded) {
                $packet->encode();
                $packet->isEncoded = true;
            }
            print_r('putPacket '.$packet->getName().PHP_EOL);

                if($packet instanceof BatchPacket){
                    $result = $packet->payload;
                }else{
                    $result = $packet->getBuffer();
                }
//            var_dump($packet instanceof BatchPacket ? 0xfe.$packet->payload : $packet->getBuffer() );
            $result = $packet instanceof BatchPacket ? 0xfe.$packet->payload : $packet->getBuffer();
            $result .=  "\x00";

            $pk->mcpeBuffer = $result;
//            var_dump($packet);
//            var_dump($pk->mcpeBuffer);
            $this->synapseInterface->putPacket($pk);
        }
//        $this->synapseInterface->putPacket($packet);
//        $this->synapseInterface->getPutPacketThread()->addMainToThread($player, $packet, $needACK, $immediate);
    }

    public function shutdown() {
    }

    public function start() {
    }

}