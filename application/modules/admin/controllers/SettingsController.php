<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_SettingsController extends Zend_Controller_Action {

    private function sendTestMail($settings, $recipient) {
        $validator = new Zend_Validate_EmailAddress();
        if (!$validator->isValid($recipient)) {
            $this->_helper->message('error_invalid_mail');
            return;
        }
        $mail = new Zend_Mail('utf-8');
        $mail->setFrom($settings['mail_from_email'], $settings['mail_from_name']);
        $mail->addTo($recipient);
        $mail->setSubject('Test mail from ' . $settings['sitename']);
        $user = Zend_Registry::get('user');
        $mail->setBodyText('This test mail was send by ' . $user->username . ' at ' . $this->getRequest()->getHttpHost());
        if ($mail->send()) {
            $this->_helper->log('info', 'mail', 'A test mail was send.');
            // Zend_Registry::get('user')->name
            $this->_helper->message($this->view->translate('info_test_mail_send'));
        } else {
            $this->_helper->log('error', 'mail', 'Failed to send a test mail to ' . $recipient);
            $this->_helper->message('error_mail_send');
        }
    }

    public function indexAction() {
        $settings = Model_SettingsMapper::getSettings();
        if ($this->_getParam('testMailReceiver')) {
            $this->sendTestMail($settings, trim($this->_getParam('testMailReceiver')));
        }
        $logArray = [
            0 => 'emergency',
            1 => 'alert',
            2 => 'critical',
            3 => 'error',
            4 => 'warn',
            5 => 'notice',
            6 => 'info',
            7 => 'debug'
        ];
        $groups[_('general')] = [
            'sitename' => $settings['sitename'],
            'language' => Model_LanguageMapper::getById($settings['language'])->name,
            'maintenance' => $settings['maintenance'] ? $this->view->ucstring('on') : $this->view->ucstring('off'),
            'offline' => $settings['offline'] ? $this->view->ucstring('on') : $this->view->ucstring('off'),
            'log_level' => $logArray[$settings['log_level']],
            'default_table_rows' => $settings['default_table_rows'],
        ];
        $groups[_('mail')] = [
            'mail' => $settings['mail'] ? $this->view->ucstring('on') : $this->view->ucstring('off'),
            'mail_transport_username' => $settings['mail_transport_username'],
            'mail_transport_ssl' => $settings['mail_transport_ssl'],
            'mail_transport_auth' => $settings['mail_transport_auth'],
            'mail_transport_port' => $settings['mail_transport_port'],
            'mail_transport_host' => $settings['mail_transport_host'],
            'mail_from_email' => $settings['mail_from_email'],
            'mail_from_name' => $settings['mail_from_name'],
            'mail_recipients_login' => $settings['mail_recipients_login'],
            'mail_recipients_feedback' => $settings['mail_recipients_feedback'],
        ];
        $groups[_('authentication')] = [
            'random_password_length' => $settings['random_password_length'],
            'reset_confirm_hours' => $settings['reset_confirm_hours'],
            'failed_login_tries' => $settings['failed_login_tries'],
            'failed_login_forget_minutes' => $settings['failed_login_forget_minutes']
        ];
        $this->view->groups = $groups;
        $this->view->settings = $settings;
    }

    public function updateAction() {
        $form = new Admin_Form_Settings();
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->form = $form;
            return;
        }
        $settings = [];
        foreach ($this->getRequest()->getPost() as $name => $value) {
            if (in_array($name, ['mail_recipients_login', 'mail_recipients_feedback'])) {
                $recipients = [];
                $validator = new Zend_Validate_EmailAddress();
                foreach (explode(',', $value) as $recipient) {
                    if ($validator->isValid(trim($recipient))) {
                        $recipients[] = trim($recipient);
                    }
                }
                $value = implode(',', $recipients);
            }
            $settings[$name] = $value;
        }
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        Model_SettingsMapper::updateSettings($settings);
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->log('info', 'admin', 'Updated settings');
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/settings');
    }

}
