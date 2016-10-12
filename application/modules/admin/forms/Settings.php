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
        ]);
        $this->addElement('password', 'mail_transport_password_retype', [
            'label' => $this->getView()->ucstring('mail_transport_password_retype'),
            'value' => $settings['mail_transport_password'],
        ]);
        $this->addElement('select', 'mail_transport_type', [
            'label' => $this->getView()->ucstring('mail_transport_type'),
            'multiOptions' => ['' => 'SMTP']
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
            'mail_transport_host' => '',
            'mail_from_email' => 'office@openatlas.eu',
            'mail_from_name' => 'OpenAtlas',
            'mail_recipients_login' => 'al@xyz.eu, bo@xyz.eu',
            'mail_recipients_feedback' => 'al@xyz.eu, bo@xyz.eu',
        ] as $name => $placeholder) {
            $this->addElement('text', $name, [
                'label' => $view->ucstring($name),
                'value' => $settings[$name],
                'placeholder' => $placeholder,
            ]
        );
        }
        foreach ([
            'mail',
            'maintenance',
            'offline',
        ] as $name) {
            $element = $this->createElement('select', $name, ['label' => $view->ucstring($name)]);
            $element->addMultiOptions([0 => $view->ucstring('off'), 1 => $view->ucstring('on')]);
            $element->setValue($settings[$name]);
            $this->addElement($element);
        }
        foreach (Model_LanguageMapper::getAll() as $language) {
            $languages[$language->id] = $language->name;
        }
        $language = $this->createElement('select', 'language', ['label' => $view->ucstring('language')]);
        $language->addMultiOptions($languages);
        $language->setValue($settings['language']);
        $this->addElement($language);
        $logLevel = $this->createElement('select', 'log_level', ['label' => $view->ucstring('log_level')]);
        $logLevel->addMultiOptions([
            0 => $view->ucstring('emergency'),
            1 => $view->ucstring('alert'),
            2 => $view->ucstring('critical'),
            3 => $view->ucstring('error'),
            4 => $view->ucstring('warn'),
            5 => $view->ucstring('notice'),
            6 => $view->ucstring('info'),
            7 => $view->ucstring('debug'),
        ]);
        $logLevel->setValue($settings['log_level']);
        $this->addElement($logLevel);
        $rows = $this->createElement('select', 'default_table_rows', ['label' => $view->ucstring('default_table_rows')]);
        $rows->addMultiOptions([10 => 10, 20 => 20, 50 => 50, 100 => 100]);
        $rows->setValue($settings['default_table_rows']);
        $this->addElement($rows);
        $this->addElement('button', 'formSubmit', ['label' => $view->ucstring('save'), 'type' => 'submit']);
        $this->setElementFilters(['StringTrim']);
    }

}
