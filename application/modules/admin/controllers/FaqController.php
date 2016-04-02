<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_FaqController extends Zend_Controller_Action {

    public function indexAction() {
        $this->view->content = Model_ContentMapper::getById(5)->getText('text');
    }
}
