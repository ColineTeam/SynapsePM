<?php
declare(strict_types=1);

namespace synapsepm\network;

use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\SourceInterface;
use pocketmine\Player;
use synapsepm\network\protocol\spp\RedirectPacket;
use synapsepm\Synapse;


class SynLibInterface implements SourceInterface {
    private $synapseInterface;
    private $synapse;

    public function __construct(Synapse $synapse, SynapseInterface $interface) {
        $this->synapse = $synapse;
        $this->synapseInterface = $interface;
    }

    public function getSynapse(): Synapse {
        return $this->synapse;
    }

    public function emergencyShutdown() {
    }

    public function setName(string $name) {
    }

    public function process(): bool {
        return false;
    }

    public function close(Player $player, string $reason = 'unknown reason') {
    }

    public function putPacket(Player $player, DataPacket $packet, bool $needACK = false, bool $immediate = true) {
        $this->synapseInterface->getPutPacketThread()->addMainToThread($player, $packet, $needACK, $immediate);
        return null;
    }

    public function shutdown() {
    }
    public function start(){}
}