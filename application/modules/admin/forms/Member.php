<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Member extends Admin_Form_Base {

    public function init() {
        $this->setName('relationForm')->setMethod('post');
        $this->setAction($this->getView()->url());
        $this->addDates(['begin', 'begin2', 'end', 'end2']);
        $this->addElement('hidden', 'typeId', ['decorators' => ['ViewHelper']]);
        $this->addElement('text', 'typeButton', [
            'label' => 'Actor Function',
            'class' => 'tableSelect',
            'readonly' => true,
            'onfocus' => 'this.blur()',
            'placeholder' => $this->getView()->ucstring('select'),
            'attribs' => ['readonly' => 'true'],
        ]);
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
        if (in_array(Zend_Controller_Front::getInstance()->getRequest()->getActionName(), ['insert', 'member'])) {
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
