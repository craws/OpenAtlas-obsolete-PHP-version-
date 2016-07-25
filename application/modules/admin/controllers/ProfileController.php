<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_ProfileController extends Zend_Controller_Action {

    public function indexAction() {
        $user = Zend_Registry::get('user');
        $form = new Admin_Form_Display();
        $form->populate([
            'theme' => $user->getSetting('theme'),
            'layout' => $user->getSetting('layout'),
        ]);
        if ($user->getSetting('language')) {
            $form->populate(['language' => $user->getSetting('language')]);
        }
        if ($user->getSetting('table_rows')) {
           $form->populate(['tableRows' => $user->getSetting('table_rows')]);
        }
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->form = $form;
            $this->view->user = $user;
            return;
        }
        $user->settings['theme'] = $form->getValue('theme');
        $user->settings['layout'] = $form->getValue('layout');
        $user->settings['language'] = $form->getValue('language');
        $user->settings['table_rows'] = $form->getValue('tableRows');
        Model_UserMapper::updateSettings($user);
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/profile');
    }

    public function passwordAction() {
        $form = new Admin_Form_Password();
        $this->view->form = $form;
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $user = Zend_Registry::get('user');
            if (!Model_User::hasher($form->getValue('passwordCurrent'), $user->password)) {
                $this->_helper->message('error_wrong_password');
                return;
            }
            if ($form->getValue('password') != $form->getValue('passwordRetype')) {
                $this->_helper->message('error_password_retype');
                return;
            }
            $user->password = Model_User::hasher($form->getValue('password'));
            Model_UserMapper::updatePassword($user);
            $this->_helper->log('info', 'user', 'Updated password');
            $this->_helper->message('info_password_update');
            return $this->_helper->redirector->gotoUrl('/admin/profile');
        }
    }

    public function updateAction() {
        $user = Zend_Registry::get('user');
        $form = new Admin_Form_Profile();
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $form->populate([
                'email' => $user->email,
                'realName' => $user->realName,
                'show_email' => $user->getSetting('show_email'),
                'newsletter' => $user->getSetting('newsletter')
            ]);
            $this->view->form = $form;
            return;
        }
        // @codeCoverageIgnoreStart
        if ($user->email != $form->getValue('email') && Model_UserMapper::getByEmail($form->getValue('email'))) {
            $this->view->form = $form;
            $this->_helper->message('error_email_exists');
            return;
        }
        // @codeCoverageIgnoreEnd
        $user->settings['show_email'] = $form->getValue('show_email');
        $user->settings['newsletter'] = $form->getValue('newsletter');
        $user->email = $form->getValue('email');
        $user->realName = $form->getValue('realName');
        $user->update();
        Model_UserMapper::updateSettings($user);
        $this->_helper->log('info', 'user', 'Updated profile');
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/profile');
    }

}
