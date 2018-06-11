<?php

namespace synapsepm\utils;

/**
 * Created by boybook on 16/6/25.
 */
class ClientData {

    public $clientList = array();

    public function getHashByDescription($description) {
        $re = null;
        foreach($this->clientList as $hash => $entry){
            if ($entry->getDescription() == $description) {
                $re = $hash;
            }
        }
        return $re;
    }
