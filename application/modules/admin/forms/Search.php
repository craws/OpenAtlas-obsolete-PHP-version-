<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Search extends Craws\Form\Table {

    public function init() {
        $this->setName('searchForm')->setMethod('post');
        $this->setAction($this->getView()->url());
        $this->addElement('hidden', 'optionToggle', ['decorators' => ['ViewHelper']]);
        $this->setElementFilters(['StringTrim']);
        $this->addElement('text', 'term', [
            'label' => $this->getView()->ucstring('search_term'),
        ]);
        $classes = ['event', 'source', 'actor', 'place', 'reference'];
        $classElement = new Zend_Form_Element_MultiCheckbox('class');
        foreach ($classes as $class) {
            $classElement->addMultiOption($class, $this->getView()->ucstring($class), ['checked' => 'checked']);
        }
        $this->addElement($classElement);
        $this->addElement('checkbox', 'searchDescription', [
            'checkedValue' => 1,
            'uncheckedValue' => 0,
            'value' => 0,
        ]);
        $this->addElement('checkbox', 'searchOwn', [
            'checkedValue' => 1,
            'uncheckedValue' => 0,
            'value' => 0,
        ]);
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring('search'), 'type' => 'submit']);
        $this->setElementFilters(['StringTrim']);
    }
}
