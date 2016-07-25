<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class ModelController extends Zend_Controller_Action {

    public function indexAction() {
        $form = new Admin_Form_Test();
        $classes = Zend_Registry::get('classes');
        $properties = Zend_Registry::get('properties');
        $this->view->count = [];
        $this->view->count['classes'] = count($classes);
        $this->view->count['properties'] = count($properties);
        $this->view->form = $form;
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            return;
        }
        $domain = $classes[$this->_getParam('domain')];
        $range = $classes[$this->_getParam('range')];
        $property = $properties[$this->_getParam('property')];
        $whitelistDomains = Zend_Registry::get('config')->get('linkcheckIgnoreDomains')->toArray();
        $this->view->testResult = [];
        if (!in_array($domain->code, $property->domain->getSubRecursive())) {
            $this->view->testResult['domainError'] = true;
        }
        if (!in_array($range->code, $property->range->getSubRecursive())) {
            $this->view->testResult['rangeError'] = true;
        }
        if (in_array($domain->code, $whitelistDomains)) {
            $this->view->testResult['domainWhitelist'] = true;
        }
        $this->view->testResult['domain'] = $domain;
        $this->view->testResult['property'] = $property;
        $this->view->testResult['range'] = $range;
    }

}
