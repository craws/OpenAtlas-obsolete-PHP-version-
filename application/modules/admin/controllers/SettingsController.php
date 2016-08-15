<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_SettingsController extends Zend_Controller_Action {

    public function indexAction() {
        $this->view->settings = Model_SettingsMapper::getSettings();
    }

    public function updateAction() {
        $form = new Admin_Form_Settings();
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->form = $form;
            return;
        }
        $settings = [];
        foreach ($this->getRequest()->getPost() as $groupAndName => $value) {
            $array = explode("__", $groupAndName);
            $settings[$array[0]][$array[1]] = $value;
        }
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        Model_SettingsMapper::updateSettings($settings);
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->log('info', 'admin', 'Updated settings');
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/settings');
    }

}
