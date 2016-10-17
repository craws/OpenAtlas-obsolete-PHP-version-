<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_SettingsController extends Zend_Controller_Action {

    // @codeCoverageIgnoreStart
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
        $mail->setBodyText('This test mail was sent by ' . $user->username . ' at ' . $this->getRequest()->getHttpHost());
        if ($mail->send()) {
            $this->_helper->log('info', 'mail', 'A test mail was sent to ' . $recipient);
            $this->_helper->message($this->view->translate('info_test_mail_send'), $recipient);
        } else {
            $this->_helper->log('error', 'mail', 'Failed to send a test mail to ' . $recipient);
            $this->_helper->message($this->view->translate('error_test_mail_send'), $recipient);
        }
    }
    // @codeCoverageIgnoreEnd

    public function indexAction() {
        $settings = Model_SettingsMapper::getSettings();
        // @codeCoverageIgnoreStart
        if ($this->_getParam('testMailReceiver')) {
            $this->sendTestMail($settings, trim($this->_getParam('testMailReceiver')));
        }
        // @codeCoverageIgnoreEnd
        $logArray = [
            0 => 'Emergency',
            1 => 'Alert',
            2 => 'Critical',
            3 => 'Error',
            4 => 'Warn',
            5 => 'Notice',
            6 => 'Info',
            7 => 'Debug'
        ];
        $groups[_('general')] = [
            'sitename' => $settings['sitename'],
            'default_language' => Model_LanguageMapper::getById($settings['default_language'])->name,
            'default_table_rows' => $settings['default_table_rows'],
            'log_level' => $logArray[$settings['log_level']],
            'maintenance' => $settings['maintenance'] ? $this->view->ucstring('on') : $this->view->ucstring('off'),
            'offline' => $settings['offline'] ? $this->view->ucstring('on') : $this->view->ucstring('off'),
        ];
        $groups[_('mail')] = [
            'mail' => $settings['mail'] ? $this->view->ucstring('on') : $this->view->ucstring('off'),
            'mail_transport_username' => $settings['mail_transport_username'],
            'mail_transport_host' => $settings['mail_transport_host'],
            'mail_transport_port' => $settings['mail_transport_port'],
            'mail_transport_type' => $settings['mail_transport_type'],
            'mail_transport_ssl' => $settings['mail_transport_ssl'],
            'mail_transport_auth' => $settings['mail_transport_auth'],
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
            $groups[_('general')] = [
                _('sitename') =>  '',
                _('default_language') =>  '',
                _('default_table_rows') =>  '',
                _('log_level') =>  '',
                _('maintenance') =>  '',
                _('offline') =>  '',
            ];
            $groups[_('mail')] = [
                _('mail') =>  '',
                _('mail_transport_username') =>  '',
                _('mail_transport_password') =>  '',
                _('mail_transport_password_retype') =>  '',
                _('mail_transport_host') =>  '',
                _('mail_transport_port') =>  '',
                _('mail_transport_type') =>  '',
                _('mail_transport_ssl') =>  '',
                _('mail_transport_auth') =>  '',
                _('mail_from_email') =>  '',
                _('mail_from_name') =>  '',
                _('mail_recipients_login') =>  _('info_mail_recipients_login'),
                _('mail_recipients_feedback') =>  _('info_mail_recipients_feedback'),

            ];
            $groups[_('authentication')] = [
                _('random_password_length') =>  '',
                _('reset_confirm_hours') =>  '',
                _('failed_login_tries') =>  '',
                _('failed_login_forget_minutes') =>  '',
            ];
            $this->view->settings = Model_SettingsMapper::getSettings();
            $this->view->groups = $groups;
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
