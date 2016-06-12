<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Relation extends Admin_Form_Base {

    public function init() {
        $this->setName('relationForm')->setMethod('post');
        $this->setAction($this->getView()->url());
        $this->addDates(['begin', 'begin2', 'end', 'end2']);
        $this->addElement('hidden', 'relatedActorIds', [
            'decorators' => ['ViewHelper'],
            'required' => true,
            'class' => 'required'
        ]);
        $this->addElement('textarea', 'description', [
            'label' => $this->getView()->ucstring('description'),
            'style' => 'width:25em;height:5em;'
        ]);
        $submitLabel = 'save';
        if (Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'insert') {
            $submitLabel = 'insert';
        }
        $this->addElement('hidden', 'continue', ['decorators' => ['ViewHelper'], 'value' => 0]);
        $this->addElement('button', 'continueButton', [
            'label' => $this->getView()->ucstring('insert_and_continue'),
            'type' => 'submit',
            'onclick' => "$('#continue').val(1);$('#relationForm').submit();return false;"
        ]);
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring($submitLabel), 'type' => 'submit']);
        $this->setElementFilters(['StringTrim']);
    }

}
