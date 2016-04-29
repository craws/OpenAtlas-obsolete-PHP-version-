<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_UserController extends Zend_Controller_Action {

    public function deleteAction() {
        $user = Model_UserMapper::getById($this->_getParam('id'));
        // @codeCoverageIgnoreStart
        if ($user->group == 'admin' && Zend_Registry::get('user')->group != 'admin') {
            $this->_helper->message('error_forbidden');
            return $this->_helper->redirector->gotoUrl('/admin/user');
        }
        // @codeCoverageIgnoreEnd
        $user->delete();
        $this->_helper->message('info_delete');
        return $this->_helper->redirector->gotoUrl('/admin/user');
    }

    public function indexAction() {
        $this->view->users = Model_UserMapper::getAll();
    }

    public function viewAction() {
        $this->view->activeUser = Zend_Registry::get('user');
        $this->view->user = Model_UserMapper::getById($this->_getParam('id'));
    }

    public function updateAction() {
        // @codeCoverageIgnoreStart
        $user = Model_UserMapper::getById($this->_getParam('id'));
        if ($user->group == 'admin' && Zend_Registry::get('user')->group != 'admin') {
            echo $this->view->ucstring('error_forbidden');
            return;
        }
        // @codeCoverageIgnoreEnd
        $this->view->user = $user;
        $form = new Admin_Form_User();
        $this->view->form = $form;
        $form->prepareUpdate($user);
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $form->populate([
                'username' => $user->username,
                'email' => $user->email,
                'active' => $user->active,
                'realName' => $user->realName,
                'info' => $user->info,
                'group' => Model_GroupMapper::getByName($user->group)->id
            ]);
            return;
        }
        // @codeCoverageIgnoreStart
        if ($user->username != $form->getValue('username') && Model_UserMapper::getByUsername($form->getValue('username'))) {
            $this->_helper->message('error_username_exists');
            return;
        }
        if ($user->email != $form->getValue('email') && Model_UserMapper::getByEmail($form->getValue('email'))) {
            $this->_helper->message('error_email_exists');
            return;
        }
        // @codeCoverageIgnoreEnd
        $user->username = $form->getValue('username');
        $user->email = $form->getValue('email');
        $user->realName = $form->getValue('realName');
        $user->info = $form->getValue('info');
        if (Zend_Registry::get('user')->id != $user->id) {
            $user->active = $form->getValue('active');
        }
        $user->group = Model_GroupMapper::getById($form->getValue('group'))->name;
        $user->update();
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/user/view/id/' . $user->id);
    }

    public function insertAction() {
        $form = new Admin_Form_User();
        $this->view->form = $form;
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            return;
        }
        if ($form->getValue('password') != $form->getValue('passwordRetype')) {
            $this->_helper->message('error_password_retype');
        }
        if (Model_UserMapper::getByUsername($form->getValue('username'))) {
            $this->_helper->message('error_username_exists');
        }
        if (Model_UserMapper::getByEmail($form->getValue('email'))) {
            $this->_helper->message('error_email_exists');
        }
        $session = new Zend_Session_Namespace("Application_Messenger");
        if ($session->messages) {
            return;
        }
        $user = new Model_User();
        $user->username = $form->getValue('username');
        $user->active = $form->getValue('active');
        $user->realName = $form->getValue('realName');
        $user->email = $form->getValue('email');
        $user->info = $form->getValue('info');
        $user->password = Model_User::hasher($form->getValue('password'));
        $user->group = Model_GroupMapper::getById($form->getValue('group'))->name;
        $user->insert();
        $this->_helper->message('info_insert');
        return $this->_helper->redirector->gotoUrl('/admin/user/view/id/' . $user->id);
    }

}
