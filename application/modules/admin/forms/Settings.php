<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Settings extends Craws\Form\Table {

    public function init() {
        $this->setName('settingsForm')->setMethod('post');
        foreach (Model_LanguageMapper::getAll() as $language) {
            $languages[$language->id] = $language->name;
        }
        foreach (Model_SettingsMapper::getSettings() as $group => $items) {
            $this->process_group($group, $items, $languages);
        }
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring('save'), 'type' => 'submit']);
        $this->setElementFilters(['StringTrim']);
    }

    private function process_group($group, $items, $languages) {
        $view = $this->getView();
        foreach ($items as $name => $value) {
            $text = new Zend_Form_Element_Text($group);
            $text->setValue('<div class="formGroupLabel">' . $view->ucstring($group) . '</div>')->helper = 'formNote';
            $this->addElement($text);
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
                    1 => $view->ucstring('emergency'),
                    2 => $view->ucstring('alert'),
                    3 => $view->ucstring('critical'),
                    4 => $view->ucstring('error'),
                    5 => $view->ucstring('warn'),
                    6 => $view->ucstring('notice'),
                    7 => $view->ucstring('info'),
                    8 => $view->ucstring('debug'),
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
