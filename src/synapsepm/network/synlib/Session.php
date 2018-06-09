<?php

namespace synapsepm\network\synlib;

//import io.netty.channel.Channel;
use synapsepm\network\protocol\spp\SynapseDataPacket;

class Session {

    public $channel;
    private $ip;
    private $port;
    private $client;
    private $lastCheck;
    private $connected;
    private $tickUseTime = 0;

    public function Session(SynapseClient $client) {
        $this->client = $client;
        $this->connected = true;
        $this->lastCheck = microtime(true);
    }

    public function updateAddress(InetSocketAddress $address) {
        $this->ip = $address->getAddress()->getHostAddress();
        $this->port = $address->getPort();
    }

    public function setConnected(bool $connected) {
        $this->connected = $connected;
    }

    public function run() {
        $this->tickProcessor();
    }

    private function tickProcessor() {
        while (!$this->client->isShutdown()) {
            $start = microtime(true);
            try {
                $this->tick();

            } catch (Exception $e) {
                $this->client->getLogger()->notice($e->getMessage());
            }
            $time =  microtime(true) - $start;
            $this->tickUseTime = $time;
            if ($time < 10) {
                try {
                    sleep(10 - $time);
                } catch (Exception $e) {
                    //ignore
                }
            }
        }
        if ($this->connected) {
            $this->client->getClientGroup()->shutdownGracefully();
        }
    }

    private function tick() {
        if ($this->update()) {
            $sendLen = 0;
            do {
                $len = $this->sendPacket();
                if ($len > 0) {
                    $sendLen += $len;
                } else {
                    break;
                }
            } while ($sendLen < 1024 * 64);
        }
    }

    private function sendPacket() {
        $packet = $this->client->readMainToThreadPacket();
        if ($packet != null) {
            $this->writePacket($packet);
            return $packet->getBuffer()->length;
        }
        return -1;
    }

    public function getHash() {
        return $this->getIp() + ":" + $this->getPort();
    }

    public function getIp() {
        return $ip;
    }

    public function getPort() {
        return $port;
    }

    public function getChannel() {
        return $channel;
    }

    public function update(){
        if ($this->client->needReconnect && $this->connected) {
            $this->connected = false;
            $this->client->needReconnect = false;
        }
        if (!$this->connected && !$this->client->isShutdown()) {
            if ((($time = microtime(true)) - $this->lastCheck) >= 3000) {//re-connect
                $this->client->getLogger()->notice("Trying to re-connect to Synapse Server");
                if ($this->client->connect()) {
                    $this->connected = true;
                    $this->client->setConnected(true);
                    $this->client->setNeedAuth(true);
                }
                $this->lastCheck = $time;
            }
            return false;
        }
        return true;
    }

    public function writePacket(SynapseDataPacket $pk) {
        if ($this->channel != null) {
            //Server.getInstance().getLogger().debug("client-ChannelWrite: pk=" + pk.getClass().getSimpleName() + " pkLen=" + pk.getBuffer().length);
            $this->channel->writeAndFlush($pk);
        }
    }

    public function getTicksPerSecond() {
        $more = $this->tickUseTime - 10;
        if ($more < 0) return 100;
        return round(10f / (float) $this->tickUseTime) * 100;
    }

}
