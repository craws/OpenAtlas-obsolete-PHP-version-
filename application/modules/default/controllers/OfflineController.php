<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class OfflineController extends Zend_Controller_Action {

    public function indexAction() {
        $offline = Model_SettingsMapper::getSetting('general', 'offline');
        $maintenance = Model_SettingsMapper::getSetting('general', 'maintenance');
        if (!$offline && !$maintenance) {
            return $this->_helper->redirector->gotoUrl('/');
        }
        $this->_helper->layout()->disableLayout();
        $this->view->offline = $offline;
        $this->view->maintenance = $maintenance;
    }

}
