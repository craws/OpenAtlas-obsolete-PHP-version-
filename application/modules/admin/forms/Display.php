<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Display extends Craws\Form\Table {

    public function init() {
        $this->setName('displayForm')->setMethod('post');
        foreach (Model_LanguageMapper::getAll() as $language) {
            $languages[$language->id] = $language->name;
        }
        $language = $this->createElement('select', 'language');
        $language->setLabel($this->getView()->ucstring('language'));
        $language->addMultiOptions($languages);
        $language->setValue(Model_SettingsMapper::getSetting('default_language'));
        $this->addElement($language);
        foreach (array_merge(['default'], glob('themes/admin/*', GLOB_ONLYDIR)) as $theme) {
            $themes[basename($theme)] = $this->getView()->ucstring(str_replace('_', ' ', basename($theme)));
        }
        $theme = $this->createElement('select', 'theme');
        $theme->setLabel($this->getView()->ucstring('theme'));
        $theme->addMultiOptions($themes);
        $this->addElement($theme);
        $layout = $this->createElement('select', 'layout');
        $layout->setLabel($this->getView()->ucstring('layout'));
        $layout->addMultiOptions([
            'default' => $this->getView()->ucstring('default'),
            'advanced' => $this->getView()->ucstring('advanced'),
        ]);
        $this->addElement($layout);
        $tableRows = $this->createElement('select', 'tableRows', ['style' => 'width:5em;']);
        $tableRows->setLabel($this->getView()->ucstring('table_rows'));
        $tableRows->setValue(Model_SettingsMapper::getSetting('default_table_rows'));
        $tableRows->addMultiOptions([10 => 10, 20 => 20, 50 => 50, 100 => 100]);
        $this->addElement($tableRows);
        $this->setElementFilters(['StringTrim']);
    }

}
