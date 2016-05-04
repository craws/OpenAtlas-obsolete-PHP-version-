<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Carrier extends Craws\Form\Table {

    public function init() {
        $this->setName('carrierForm')->setMethod('post');
        $this->setAction($this->getView()->url());
        Admin_Form_Abstract::addDates($this, ['begin', 'begin2', 'end', 'end2']);
        $this->addElement('text', 'name', [
            'class' => 'required',
            'required' => true,
            'label' => $this->getView()->ucstring('name')
        ]);
        $this->addElement('hidden', 'typeId', ['decorators' => ['ViewHelper']]);
        $this->addElement('text', 'typeButton', [
            'label' => $this->getView()->ucstring('type'),
            'class' => 'tableSelect',
            'readonly' => true,
            'onfocus' => 'this.blur()',
            'placeholder' => $this->getView()->ucstring('select'),
            'attribs' => ['readonly' => 'true'],
        ]);
        $this->addElement('hidden', 'objectId', ['decorators' => ['ViewHelper']]);
        $this->addElement('text', 'objectButton', [
            'label' => $this->getView()->ucstring('place_of_issue'),
            'class' => 'tableSelect',
            'readonly' => true,
            'onfocus' => 'this.blur()',
            'placeholder' => $this->getView()->ucstring('select'),
            'attribs' => ['readonly' => 'true'],
        ]);
        $this->addElement('textarea', 'description', ['label' => $this->getView()->ucstring('description')]);
        $submitLabel = 'save';
        if (Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'insert') {
            $submitLabel = 'insert';
        }
        $this->addElement('hidden', 'modified');
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring($submitLabel), 'type' => 'submit']);
        if (Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'insert') {
            $this->addElement('hidden', 'continue', ['decorators' => ['ViewHelper'], 'value' => 0]);
            $this->addElement('button', 'continueButton', [
                'label' => $this->getView()->ucstring('insert_and_continue'),
                'type' => 'submit',
                'onclick' => "$('#continue').val(1);$('#carrierForm').submit();return false;"
            ]);
        }
        $this->setElementFilters(['StringTrim']);
    }
}
