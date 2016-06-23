<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Reference extends Admin_Form_Base {

    public function init() {
        $this->setName('referenceForm')->setMethod('post');
        $this->setAction($this->getView()->url());
        $this->addElement('text', 'name', [
            'class' => 'required',
            'required' => true,
            'label' => $this->getView()->ucstring('name'),
            'placeholder' => 'Doe 2015'
        ]);
        $this->addElement('textarea', 'description', [
            'label' => $this->getView()->ucstring('description'),
            'placeholder' => 'Jane Doe, my first book about dinosaurs, Cambridge 2015',
        ]);
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
                'onclick' => "$('#continue').val(1);$('#referenceForm').submit();return false;"
            ]);
        }
        $this->setElementFilters(['StringTrim']);
    }

}
