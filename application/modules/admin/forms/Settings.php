<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Settings extends Craws\Form\Table {

    public function init() {
        $view = $this->getView();
        $this->setName('settingsForm')->setMethod('post');
        $settings = Model_SettingsMapper::getSettings();
        foreach([
            'sitename',
            'random_password_length',
            'reset_confirm_hours',
            'failed_login_tries',
            'failed_login_forget_minutes'] as $name) {
            $this->addElement('text', $name, ['label' => $view->ucstring($name),'value' => $settings[$name]]);
        }
        foreach (['mail', 'notify_login', 'maintenance', 'offline'] as $name) {
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
