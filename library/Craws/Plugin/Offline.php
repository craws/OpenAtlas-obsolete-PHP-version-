<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

namespace Craws\Plugin;

class Offline extends \Zend_Controller_Plugin_Abstract {

    public function routeShutdown(\Zend_Controller_Request_Abstract $request) {
        if (($request->getModuleName() != 'admin' || $request->getControllerName() != 'index') &&
            $request->getControllerName() != 'offline') {
            if (\Zend_Registry::get('user')->group != 'admin') {
                \Zend_Controller_Front::getInstance()->getResponse()->setRedirect('/offline');
            }
        }
    }

}
