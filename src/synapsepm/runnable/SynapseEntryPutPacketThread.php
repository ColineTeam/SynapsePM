<?php // https://github.com/iTXTech/SynapseAPI/blob/master/src/main/java/org/itxtech/synapseapi/runnable/SynapseEntryPutPacketThread.java
namespace synapsepm\runnable;

use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\Thread;
use \pocketmine\network\mcpe\protocol\DataPacket;
use synapsepm\network\protocol\spp\RedirectPacket;
use synapsepm\network\SynapseInterface;

class SynapseEntryPutPacketThread extends Thread {
    /* @var $synapseInterface SynapseInterface */
    private $synapseInterface;
    private $queue = [];

    private $isAutoCompress; //bool
    private $tickUseTime = 0;
    private $isRunning = true;

    public function __construct(SynapseInterface $synapseInterface) {
//    super("SynapseEntryPutPacketThread");
        $this->synapseInterface = $synapseInterface;
        $this->isAutoCompress = true;
        $this->start();
    }

    public function addMainToThread(Player $player, DataPacket $packet, bool $needACK, bool $immediate) {
       // $this->queue[] = (object)['player' => $player, 'packet' => $packet, 'needACK' => $needACK, 'immediate' => $immediate];
        $this->queue[] = new Entry($player, $packet, $needACK, $immediate);
    }

    public function run() {
        while ($this->isRunning) {
            $start = microtime();
            while (($entry = array_shift($this->queue)) !== null) {
                try {
                    if (!$entry->player->closed) {
                        $pk = new RedirectPacket();
                        $pk->uuid = $pk->player->getUniqueId();
                        $pk->direct = $entry->immediate;
                        if (!$entry->packet->isEncoded) {
                            $entry->packet->encode();
                            $entry->packet->isEncoded = true;
                        }
//                        if(!($entry->packet instanceof BatchPacket) && $this->isAutoCompress){
//                            $buff = $entry->packet->getBuffer();
//
//                        }
                        $this->synapseInterface->putPacket($pk);
                    }

                } catch (\Exception $e) {
                    $this->synapseInterface->getSynapse()->getLogger()->alert("Catch exception when Synapse Entry Put Packet: " . $e->getMessage());
                    $this->synapseInterface->getSynapse()->getLogger()->getLogger()->logException($e);
                }
                /*$tickUseTime = microtime() - $start;
                if ($tickUseTime) {
                    try {
                        // TODO: find Thread sleep
                    }
                }*/
            }
        }
    }
}
class Entry {
    private $player;
    private $packet;
    private $needACK;
    private $immediate;

    public function __construct(Player $player, DataPacket $packet, bool $needACK, bool $immediate) {
        $this->player = $player;
        $this->packet = $packet;
        $this->needACK = $needACK;
        $this->immediate = $immediate;
    }
}