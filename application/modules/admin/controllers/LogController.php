<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_LogController extends Zend_Controller_Action {

    public function indexAction() {
        $form = new Admin_Form_LogFilter();
        $this->view->form = $form;
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $form->populate(['limit' => '300', 'user' => 'all', 'priority' => 5]);
        }
        $this->view->entries = Model_LogMapper::getLogs($form->getValues());
    }

    public function deleteAllAction() {
        Model_LogMapper::deleteAll();
        return $this->_helper->redirector->gotoUrl('/admin/log');
    }

    public function deleteAction() {
        $log = Model_LogMapper::getById($this->_getParam('id'));
        $log->delete();
        return $this->_helper->redirector->gotoUrl('/admin/log');
    }

    public function viewAction() {
        $log = Model_LogMapper::getById($this->_getParam('id'));
        $data = [];
        $data['priority'] = $log->priority;
        $data['type'] = $log->type;
        if ($log->userId) {
            $user = Model_UserMapper::getById($log->userId);
            $data['user'] = '<a href="' . '/admin/user/view/id/' . $user->id . '">' . $user->username . '</a>';
        }
        $data['ip'] = $log->ip;
        foreach ($log->params as $key => $value) {
            $data[$key] = $value;
        }
        $data['created'] = $log->created;
        $data['agent'] = $log->agent;
        $this->view->log = $log;
        $this->view->data = $data;
    }

}
