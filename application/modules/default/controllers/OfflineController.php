<?php

class OfflineController extends Zend_Controller_Action {

    public function indexAction() {
        $offline = Model_SettingsMapper::getSetting('general', 'offline');
        $maintenance = Model_SettingsMapper::getSetting('general', 'maintenance');
        // @codeCoverageIgnoreStart
        if (!$offline && !$maintenance) {
            return $this->_helper->redirector->gotoUrl('/');
        }
        // @codeCoverageIgnoreEnd
        $this->_helper->layout()->disableLayout();
        $this->view->offline = $offline;
        $this->view->maintenance = $maintenance;
    }

}
