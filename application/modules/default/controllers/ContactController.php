<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class ContactController extends Zend_Controller_Action {

    public function indexAction() {
        $this->view->contact = Model_ContentMapper::getById(3)->getText('text');
    }

}
