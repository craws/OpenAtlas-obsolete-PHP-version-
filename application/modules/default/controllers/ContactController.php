<?php

class ContactController extends Zend_Controller_Action {

    public function indexAction() {
        $this->view->contact = Model_ContentMapper::getById(3)->getText('text');
    }

}
