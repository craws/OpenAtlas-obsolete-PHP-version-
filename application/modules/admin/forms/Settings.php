<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Settings extends Craws\Form\Table {

    public function init() {
        $this->setName('settingsForm')->setMethod('post');
        foreach (Model_LanguageMapper::getAll() as $language) {
            $languages[$language->id] = $language->name;
        }
        $settings = Model_SettingsMapper::getSettings();
        $this->addElement('text', 'sitename', [
            'label' => $this->getView()->ucstring('sitename'),
            'value' => $settings['sitename']
        ]);
        $language = $this->createElement('select', 'language', ['label' => $this->getView()->ucstring('language')]);
        $language->addMultiOptions($languages);
        $language->setValue($settings['language']);
        $this->addElement($language);
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring('save'), 'type' => 'submit']);
        $this->setElementFilters(['StringTrim']);
    }

    private function process_group($group, $items, $languages) {
        $view = $this->getView();
        foreach ($items as $name => $value) {
            $elementName = $group . '__' . $name;
            if ($elementName == 'general__language') {
                $element = $this->createElement('select', $elementName, ['label' => $this->getLabel('language')]);
                $element->addMultiOptions($languages);
                $element->setValue($value);
            } else if ($group == 'module' ||
                in_array($elementName, ['general__maintenance', 'general__offline', 'mail__notify_login'])) {
                $element = $this->createElement('select', $elementName, ['label' => $this->getLabel($name)]);
                $element->addMultiOptions([0 => $view->ucstring('off'), 1 => $view->ucstring('on')]);
                $element->setValue($value);
            } else if ($name == 'log_level') {
                $element = $this->createElement('select', $elementName, ['label' => $this->getLabel($name)]);
                $element->addMultiOptions([
                    0 => $view->ucstring('emergency'),
                    1 => $view->ucstring('alert'),
                    2 => $view->ucstring('critical'),
                    3 => $view->ucstring('error'),
                    4 => $view->ucstring('warn'),
                    5 => $view->ucstring('notice'),
                    6 => $view->ucstring('info'),
                    7 => $view->ucstring('debug'),
                ]);
                $element->setValue($value);
            } else if ($name == 'default_table_rows') {
                $element = $this->createElement('select', $elementName, ['label' => $this->getLabel($name)]);
                $element->addMultiOptions([10 => 10, 20 => 20, 50 => 50, 100 => 100]);
                $element->setValue($value);
            } else {
                $element = new Zend_Form_Element_Text($elementName, ['label' => $this->getLabel($name),
                    'validators' => [['StringLength', false, [1, 32]]]
                ]);
                $element->setValue($value);
            }
            $this->addElement($element);
        }
    }

    private function getLabel($name) {
        return $this->getView()->ucstring(str_replace('_', ' ', $name));
    }

}
