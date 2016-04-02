<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

namespace Craws\Controller\Helper;

class Message extends \Zend_Controller_Action_Helper_Abstract {

    const HELPER_NAME = 'Message';

    function getName() {
        return self::HELPER_NAME;
    }

    public function direct($message) {
        $session = new \Zend_Session_Namespace("Application_Messenger");
        if (!isset($session->messages)) {
            $session->messages = [];
        }
        $type = 'info';
        if (strpos($message, 'error') === 0) {
            $type = 'error';
        }
        $session->messages[] = ['type' => $type, 'message' => $message];
    }

}
