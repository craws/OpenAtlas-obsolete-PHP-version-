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

    // @codeCoverageIgnoreStart
    public function newsletterAction() {
        $form = new Admin_Form_Newsletter();
        $this->view->form = $form;
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $users = Model_UserMapper::getAll();
            $recipients = [];
            foreach ($users as $user) {
                if ($user->getSetting('newsletter') && $user->active) {
                    $recipients[] = $user;
                }
            }
            $this->view->recipients = $recipients;
            return;
        }
        $settings = Model_SettingsMapper::getSettings();
        $recipients = [];
        foreach(filter_input_array(INPUT_POST) as $key => $value) {
            if (is_int($key)) {
                $recipient = Model_UserMapper::getById($key);
                $code = Model_User::randomPassword(16);
                $recipient->unsubscribeCode = $code;
                $recipient->update();
                $link = 'http://' . $this->getRequest()->getHttpHost() . '/unsubscribe/index/code/' . $code;
                $htmlLink = '<a href="' . $link . '">' . $this->view->translate('unsubscribe') . '</a>';
                $unsubscribeText = '<br><br>' . $this->view->translate('mail_unsubscribe', $htmlLink);
                $mail = new Zend_Mail('utf-8');
                $mail->addTo($recipient->email);
                $mail->setSubject($form->getValue('subject'));
                $user = Zend_Registry::get('user');
                $body = nl2br($form->getValue('body')) . $unsubscribeText;
                $mail->setBodyHtml($body);
                $mail->setBodyText(strip_tags($body));
                if ($mail->send()) {
                    $recipients[] = $recipient->email;
                } else {
                    $this->_helper->log('error', 'mail', 'Sending newsletter failed from ' .
                        Zend_Registry::get('config')->resources->mail->transport->username) . ' to ' . $recipient->email;
                        $this->_helper->message('error_mail_send');

                }
            }
        }
        $this->_helper->log('info', 'mail', 'Newsletter mail send to ' . implode(', ', $recipients));
        $this->_helper->message($this->view->translate('info_newsletter_send', count($recipients)));
        $this->_helper->redirector->gotoUrl('/admin/user');
    }
    // @codeCoverageIgnoreEnd

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
        $form->removeElement('registrationMail');
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
        if ($user->username != $form->getValue('username') && Model_UserMapper::getByUsername($form->getValue('username'))) {
            $this->_helper->message('error_username_exists');
            return;
        }
        if ($user->email != $form->getValue('email') && Model_UserMapper::getByEmail($form->getValue('email'))) {
            $this->_helper->message('error_email_exists');
            return;
        }
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
        // @codeCoverageIgnoreStart
        if ($form->getValue('registrationMail') && $user->email) {
            $settings = Model_SettingsMapper::getSettings();
            $this->view->mailUsername = $user->username;
            $this->view->mailPassword = $form->getValue('password');
            $this->view->mailUrl = 'http://' . $this->getRequest()->getHttpHost() . '/admin';
            $mail = new Zend_Mail('utf-8');
            $mail->addTo($user->email);
            $mail->setSubject($this->view->translate('mail_registration_subject') . ' ' . $settings['sitename']);
            $mail->setBodyHtml($this->view->render('mail/registration.phtml'));
            $mail->setBodyText(strip_tags($this->view->render('mail/registration.phtml')));
            if (!$mail->send()) {
                $this->_helper->log('error', 'mail', 'Failed to send registration mail to ' . $user->email);
                $this->_helper->message('error_mail_send');
            } else {
                $this->_helper->log('info', 'mail', 'Send registration mail to ' . $user->email);
                $this->_helper->message('info_registration_mail_sent');
            }
        }
        // @codeCoverageIgnoreEnd
        return $this->_helper->redirector->gotoUrl('/admin/user/view/id/' . $user->id);
    }

}
