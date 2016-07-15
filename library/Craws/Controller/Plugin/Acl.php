<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

namespace Craws\Controller\Plugin;

class Acl extends \Zend_Controller_Plugin_Abstract {

    public function preDispatch(\Zend_Controller_Request_Abstract $request) {
        $acl = new \Craws\Acl();
        $resource = $request->getModuleName() . ":" . $request->getControllerName() . ':' . $request->getActionName();
        if (!$acl->has($resource)) {
            $request->setModuleName('default');
            $request->setControllerName('error');
            $request->setActionName('missing-acl');
            return;
        }
        $user = \Zend_Registry::get('user');
        if (!$acl->isAllowed($user->group, $resource)) {
            if ($request->getModuleName() == 'admin' && $user->group == 'guest') {
                $request->setModuleName('admin');
                $request->setControllerName('index');
                $request->setActionName('index');
                $requestUri = \Zend_Controller_Front::getInstance()->getRequest()->getRequestUri();
                $session = new \Zend_Session_Namespace('lastRequest');
                $session->lastRequestUri = $requestUri;
            }
        }
    }

}
