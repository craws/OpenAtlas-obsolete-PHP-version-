<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_IndexController extends Zend_Controller_Action {

    public function indexAction() {
        // @codeCoverageIgnoreStart
        if (Zend_Registry::get('user')->active) {
            return $this->_helper->redirector->gotoUrl('/admin/overview');
        }
        // @codeCoverageIgnoreEnd
        $form = new Admin_Form_Login();
        $this->view->form = $form;
        if (!$this->_getParam('username') || !$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            return;
        }
        $user = Model_UserMapper::getByUsername($form->getValue('username'));
        if (!$user) {
            $this->_helper->log('info', 'login non existing username', $form->getValue('username'));
            $this->_helper->message('error_login');
            return;
        }
        if (Model_User::loginAttemptsExceeded($user)) {
            $this->_helper->message('error_login_attempts_exceeded');
            return;
        }
        if (Model_User::hasher($form->getValue('password'), $user->password)) {
            $authAdapter = new Zend_Auth_Adapter_DbTable(Zend_Db_Table::getDefaultAdapter());
            $authAdapter
                ->setTableName('web.user')
                ->setIdentityColumn(new Zend_Db_Expr('LOWER(username)'))
                ->setIdentity(strtolower($user->username))
                ->setCredentialColumn('password')
                ->setCredentialTreatment('? AND active = 1')
                ->setCredential($user->password);
            $auth = Zend_Auth::getInstance();
            $result = $auth->authenticate($authAdapter);
            if ($result->isValid()) {
                $this->login($user, $auth, $authAdapter);
                return $this->_helper->redirector->gotoUrl('/admin/overview');
                // @codeCoverageIgnoreStart
            }
        }
        // @codeCoverageIgnoreEnd
        $user->loginLastFailure = new Zend_Date();
        $user->loginFailedCount = $user->loginFailedCount + 1;
        $user->update();
        $this->_helper->log('info', 'login failed', $user->username);
        $this->_helper->message('error_login');
        return;
    }

    private function login($user, $auth, $authAdapter) {
        $identity = $authAdapter->getResultRowObject();
        $auth->getStorage()->write($identity);
        /* write login info in session because it will change afterwards */
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->lastLogin = '';
        // @codeCoverageIgnoreStart
        if ($user->loginLastSuccess) {
            $defaultNamespace->lastLogin = $user->loginLastSuccess;
        }
        // @codeCoverageIgnoreEnd
        $defaultNamespace->failedLoginCount = $user->loginFailedCount;
        Zend_Registry::set('user', $user);
        $user->loginLastSuccess = new Zend_Date();
        $user->loginFailedCount = 0;
        $user->update();
        $this->_helper->log('info', 'login');
        // @codeCoverageIgnoreStart
        if (!strpos(filter_input(INPUT_SERVER, 'HTTP_HOST'), 'local') &&
            Model_SettingsMapper::getSetting('module', 'mail') &&
            Model_SettingsMapper::getSetting('mail', 'notify_login')
        ) {
            $message = "Login from " . $user->username . "(" . $user->id . ") at " .
                Model_SettingsMapper::getSetting('general', 'sitename') . "(" . $this->getRequest()->getHttpHost() .
                ").\r\n\r";
            $mail = new Zend_Mail('utf-8');
            foreach(Zend_Registry::get('config')->get('mailRecipientsLogin')->toArray() as $receiver) {
                $mail->addTo($receiver);
            }
            $mail->setSubject('Login ' . $user->username . ' on ' . $this->getRequest()->getHttpHost());
            $mail->setBodyText($message);
            if (!$mail->send()) {
                $this->_helper->log('error', 'mail', 'Failed to send login info mail');
            }
        }
        $session = new Zend_Session_Namespace('lastRequest');
        if (isset($session->lastRequestUri)) {
            $this->_redirect($session->lastRequestUri);
            return;
        }
        // @codeCoverageIgnoreEnd
    }

    public function logoutAction() {
        // @codeCoverageIgnoreStart
        if (!Zend_Registry::get('user')->username) {
            return $this->_helper->redirector->gotoUrl('/admin');  // not logged in anymore
        }
        // @codeCoverageIgnoreEnd
        $this->_helper->log('info', 'logout', Zend_Registry::get('user')->username);
        Zend_Auth::getInstance()->clearIdentity();
        Zend_Registry::set('user', new Model_User());
        return $this->_helper->redirector->gotoUrl('/admin');
    }

    // @codeCoverageIgnoreStart

    public function resetConfirmAction() {
        $user = Model_UserMapper::getByResetCode($this->_getParam('code'));
        if (!$user) {
            $this->_helper->log('error', 'password', 'Reset confirm with illegal code: ' . $this->_getParam('code'));
            $this->_helper->message("error_invalid_request");
            return $this->_helper->redirector->gotoUrl('/admin');
        }
        $resetDate = $user->passwordResetDate;
        $resetDate->addHour(Model_SettingsMapper::getSetting('authentication', 'reset_confirm_hours'));
        if (!$resetDate->isLater(new Zend_Date())) {
            $this->_helper->log('error', 'password', 'Password reset confirmed too late by ' . $user->id);
            $this->_helper->message('error_reset_outdated');
            return $this->_helper->redirector->gotoUrl('/admin');
        }
        $password = Model_User::randomPassword(Model_SettingsMapper::getSetting('authentication', 'random_password_length'));
        $hash = Model_User::hasher($password);
        $user->password = $hash;
        $user->passwordResetCode = null;
        $user->passwordResetDate = null;
        $user->update();
        Model_UserMapper::updatePassword($user);
        $this->view->mailUsername = $user->username;
        $this->view->mailPassword = $password;
        $this->view->mailUrl = 'http://' . $this->getRequest()->getHttpHost() . '/admin';
        $mail = new Zend_Mail('utf-8');
        $mail->addTo($user->email);
        $mail->setSubject($this->view->translate('mail_new_password_subject'));
        $mail->setBodyHtml($this->view->render('mail/reset-confirm.phtml'));
        $mail->setBodyText(strip_tags($this->view->render('mail/reset-confirm.phtml')));
        if ($mail->send()) {
            $this->_helper->log('info', 'mail', 'New password mail to ' . $user->email . ' for ' . $user->username);
            $this->_helper->message($this->view->translate('info_mail_new_password_send', $user->email));
        } else {
            $this->_helper->log('error', 'mail', 'Failed to send new password to email ' . $user->email . ' for ' . $user->username);
            $this->_helper->message('error_mail_send');
        }
        return $this->_helper->redirector->gotoUrl('/admin');
    }

    public function resetPasswordAction() {
        $form = new Admin_Form_PasswordReset();
        $this->view->form = $form;
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $email = mb_strtolower($form->getValue('email'));
            $user = Model_UserMapper::getByEmail($email);
            if (!$user) {
                $this->_helper->log('error', 'password', 'Requested password reset for nonexisting email ' . $email);
                $this->_helper->message('error_nonexist_email');
                return;
            }
            $resetCode = Model_User::randomPassword(Model_SettingsMapper::getSetting('authentication', 'random_password_length'));
            $user->passwordResetCode = $resetCode;
            $user->passwordResetDate = new Zend_Date();
            $user->update();
            $this->view->mailLink = 'http://' . $this->getRequest()->getHttpHost() . "/admin/index/reset-confirm/code/" . $resetCode;
            $this->view->mailUsername = $user->username;
            $this->view->mailHost = $this->getRequest()->getHttpHost();
            $this->view->mailResetConfirmHours = Model_SettingsMapper::getSetting('authentication', 'reset_confirm_hours');
            $mail = new Zend_Mail('utf-8');
            $mail->addTo($email);
            $mail->setSubject($this->view->translate('mail_reset_password_subject'));
            $mail->setBodyHtml($this->view->render('mail/reset-password.phtml'));
            $mail->setBodyText(strip_tags($this->view->render('mail/reset-password.phtml')));
            if ($mail->send()) {
                $this->_helper->log('info', 'mail', 'Password reset confirm mail send to ' . $user->username);
                $this->_helper->message($this->view->translate('info_mail_password_confirmed_send', $email));
                $this->_helper->redirector->gotoUrl('/admin');
            } else {
                $this->_helper->log('error', 'mail', 'Failed to send password reset confirmation mail to ' . $email);
                $this->_helper->message('error_mail_send');
            }
        }
    }
    // @codeCoverageIgnoreEnd
}
