<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Settings extends Craws\Form\Table {

    public function init() {
        $view = $this->getView();
        $this->setName('settingsForm')->setMethod('post');
        $settings = Model_SettingsMapper::getSettings();
        $this->addElement('password', 'mail_transport_password', [
            'label' => $this->getView()->ucstring('mail_transport_password'),
            'value' => $settings['mail_transport_password'],
            'renderPassword' => true
        ]);
        $this->addElement('password', 'mail_transport_password_retype', [
            'label' => $this->getView()->ucstring('mail_transport_password_retype'),
            'value' => $settings['mail_transport_password'],
            'renderPassword' => true
        ]);
        $this->addElement('select', 'mail_transport_type', [
            'label' => $this->getView()->ucstring('mail_transport_type'),
            'multiOptions' => ['SMTP' => 'SMTP']
        ]);
        foreach ([
            'sitename' => '',
            'random_password_length' => '',
            'reset_confirm_hours' => '',
            'failed_login_tries' => '3',
            'failed_login_forget_minutes' => '',
            'mail_transport_username' => '',
            'mail_transport_ssl' => 'tls',
            'mail_transport_auth' => 'plain',
            'mail_transport_port' => '25',
            'mail_transport_host' => 'localhost',
            'mail_from_email' => 'office@openatlas.eu',
            'mail_from_name' => 'OpenAtlas',
            'mail_recipients_login' => 'a@xyz.eu, b@xyz.eu',
            'mail_recipients_feedback' => 'a@xyz.eu, b@xyz.eu',
        ] as $name => $placeholder) {
            $this->addElement('text', $name, [
                'label' => $view->ucstring($name),
                'value' => $settings[$name],
                'placeholder' => $placeholder,
            ]
        );
        }
        foreach (['mail', 'maintenance', 'offline'] as $name) {
            $this->addElement('radio', $name, [
                'label' => $view->ucstring($name),
                'value' => $settings[$name],
                'separator' => ' ',
                'multiOptions' => [1 => $view->ucstring('on'), 0 => $view->ucstring('off')],
            ]);
        }
        foreach (Model_LanguageMapper::getAll() as $language) {
            $languages[$language->id] = $language->name;
        }
        $this->addElement('select', 'default_language', [
            'label' => $view->ucstring('default_language'),
            'multiOptions' => $languages,
            'value' => $settings['default_language']
        ]);
        $this->addElement('select', 'log_level', [
            'label' => $view->ucstring('log_level'),
            'value' => $settings['log_level'],
            'multiOptions' => [
                0 => $view->ucstring('emergency'),
                1 => $view->ucstring('alert'),
                2 => $view->ucstring('critical'),
                3 => $view->ucstring('error'),
                4 => $view->ucstring('warn'),
                5 => $view->ucstring('notice'),
                6 => $view->ucstring('info'),
                7 => $view->ucstring('debug'),
            ]
        ]);
        $this->addElement('select', 'default_table_rows', [
            'label' => $view->ucstring('default_table_rows'),
            'value' => $settings['default_table_rows'],
            'multiOptions' => [10 => 10, 20 => 20, 50 => 50, 100 => 100],
        ]);
        $this->addElement('button', 'formSubmit', ['label' => $view->ucstring('save'), 'type' => 'submit']);
        $this->setElementFilters(['StringTrim']);
    }

}
