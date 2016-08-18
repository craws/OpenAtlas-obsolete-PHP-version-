<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class AboutController extends Zend_Controller_Action {

    public function indexAction() {
        $this->view->intro = Model_ContentMapper::getById(1)->getText('text');
    }

}
