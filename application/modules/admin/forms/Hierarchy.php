<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Hierarchy extends Craws\Form\Table {

    public function init() {
        $this->setAction($this->getView()->url());
        $this->setName('hierarchyForm')->setMethod('post');
        $this->addElement('text', 'name', [
            'label' => $this->getView()->ucstring('name'),
            'required' => true,
            'class' => 'required',
        ]);
        $this->addElement('checkbox', 'multiple', [
            'label' => $this->getView()->ucstring('multiple'),
            'checkedValue' => 1,
            'uncheckedValue' => 0,
            'value' => 0,
        ]);
        $this->addElement('textarea', 'description', ['label' => $this->getView()->ucstring('description')]);
        $formsElement = new Zend_Form_Element_MultiCheckbox('forms');
        foreach (Zend_Registry::get('forms') as $formName => $form) {
            $formsElement->addMultiOption($form['id'], $formName);
        }
        $this->addElement($formsElement);
        $this->addElement('textarea', 'description', ['label' => $this->getView()->ucstring('description')]);
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring('save'), 'type' => 'submit']);
        $this->setElementFilters(['StringTrim']);
    }

}
