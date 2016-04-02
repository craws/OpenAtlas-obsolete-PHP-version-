<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_Messenger extends Zend_View_Helper_Abstract {

    public function messenger() {
        $session = new Zend_Session_Namespace("Application_Messenger");
        if (!isset($session->messages)) {
            $session->messages = [];
        }
        $messages = $session->messages;
        unset($session->messages);
        return $messages;
    }

}
