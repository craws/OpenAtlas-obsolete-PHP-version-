<?php

class IndexController extends Zend_Controller_Action {

    public function indexAction() {
        $this->view->intro = Model_ContentMapper::getById(1)->getText('text');
    }

}
