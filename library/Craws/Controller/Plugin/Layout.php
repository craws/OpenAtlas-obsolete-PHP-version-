<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

namespace Craws\Controller\Plugin;

class Layout extends \Zend_Controller_Plugin_Abstract {

    public function routeShutdown(\Zend_Controller_Request_Abstract $request) {
        \Zend_Layout::getMvcInstance()->setLayout($request->getModuleName());
    }

}
