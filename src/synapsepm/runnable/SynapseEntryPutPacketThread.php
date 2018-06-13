<?php // https://github.com/iTXTech/SynapseAPI/blob/master/src/main/java/org/itxtech/synapseapi/runnable/SynapseEntryPutPacketThread.java
namespace synapsepm\runnable;

use pocketmine\network\mcpe\protocol\BatchPacket;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\network\mcpe\protocol\DataPacket;
use synapsepm\network\protocol\spp\RedirectPacket;
use synapsepm\network\SynapseInterface;

class SynapseEntryPutPacketThread extends \pocketmine\Thread {
    /* @var $synapseInterface SynapseInterface */
    private $synapseInterface;
    private $queue = [];

    private $isAutoCompress; //bool
    private $tickUseTime = 0;
    private $isRunning = true;

    public function __construct(SynapseInterface $synapseInterface) {
//    super("SynapseEntryPutPacketThread");
//        parent::
//        $this->synapseInterface = $synapseInterface; //проблема тут!
        $this->isAutoCompress = true;
        Server::getInstance()->getTickSleeper()->addNotifier($notifier = new \pocketmine\snooze\SleeperNotifier(), function (): void {
            $this->sendPackets();
        });
        $this->notifier = $notifier;
        $this->start();
    }
    public function sendPackets(){
        var_dump($this->queue);
    }

    public function addMainToThread(Player $player, DataPacket $packet, bool $needACK, bool $immediate) {
        $this->queue[] = (object)['player' => $player, 'packet' => $packet, 'needACK' => $needACK, 'immediate' => $immediate];
    }

    public function run() {
        while ($this->isRunning) {
            $start = microtime();
            if (count($this->queue) > 0){
                $arr = array_values(get_object_vars($this->queue));
                $elem = count($arr) -1;
                unset($this->queue[$elem]);
                for ($i = 0; $i <= $elem+1; $i++) {
                    $this->queue[$i] = $this->queue[$i+1];
                }
            }
            while (($entry = @$arr[$elem]) !== null) {
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
                        $this->notifier->wakeupSleeper();
//                        $this->synapseInterface->putPacket($pk);
                    }

                } catch (\Exception $e) {
                    Server::getInstance()->getLogger()->alert("Catch exception when Synapse Entry Put Packet: " . $e->getMessage());
                    Server::getInstance()->getLogger()->logException($e);
                }
                $tickUseTime = microtime() - $start;
                if ($tickUseTime < 10) {
                    sleep(10 - $tickUseTime);
                }
            }
        }
    }
}
class Entry {
    private $player;
    private $packet;
    private $needACK;
    private $immediate;

    public function __construct(Player $player, DataPacket $packet, boolean $needACK, boolean $immediate) {
        $this->player = $player;
        $this->packet = $packet;
        $this->needACK = $needACK;
        $this->immediate = $immediate;
    }
}