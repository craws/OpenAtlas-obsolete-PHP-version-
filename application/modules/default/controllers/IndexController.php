<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class IndexController extends Zend_Controller_Action {

    public function indexAction() {
        return $this->_helper->redirector->gotoUrl('/about');
    }

}
