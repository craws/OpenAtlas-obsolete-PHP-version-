<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Source extends Craws\Form\Table {

    public function init() {
        $this->setName('sourceForm')->setMethod('post');
        $this->setAction($this->getView()->url());
        $this->addElement('hidden', 'typeId', ['decorators' => ['ViewHelper']]);
        $this->addElement('text', 'typeButton', [
            'label' => $this->getView()->ucstring('type'),
            'class' => 'tableSelect',
            'readonly' => true,
            'onfocus' => 'this.blur()',
            'placeholder' => $this->getView()->ucstring('select'),
            'attribs' => ['readonly' => 'true'],
        ]);
        $this->addElement('text', 'name', [
            'label' => $this->getView()->ucstring('name'),
            'class' => 'required',
            'required' => true,
        ]);
        $this->addElement('textarea', 'description', ['label' => $this->getView()->ucstring('content')]);
        $submitLabel = 'save';
        if (Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'insert') {
            $submitLabel = 'insert';
        }
        $this->addElement('hidden', 'modified');
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring($submitLabel), 'type' => 'submit']);
        $this->addElement('hidden', 'continue', ['decorators' => ['ViewHelper'], 'value' => 0]);
        $this->addElement('button', 'continueButton', [
            'label' => $this->getView()->ucstring('insert_and_continue'),
            'type' => 'submit',
            'onclick' => "$('#continue').val(1);$('#sourceForm').submit();return false;"
        ]);
        $this->setElementFilters(['StringTrim']);
    }

}
