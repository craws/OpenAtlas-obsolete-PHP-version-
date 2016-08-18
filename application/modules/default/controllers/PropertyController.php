<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class PropertyController extends Zend_Controller_Action {

    public function init() {
        $this->view->menuHighlight = 'model';
    }

    public function indexAction() {
        $this->view->properties = Model_PropertyMapper::getAll();
    }

    public function viewAction() {
        $this->view->property = Model_PropertyMapper::getById($this->_getParam('id'));
    }

}
