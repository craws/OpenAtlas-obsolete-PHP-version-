<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Actor extends Craws\Form\Table {

    public function init() {
        $this->setName('actorForm')->setMethod('post');
        $this->setAction($this->getView()->url());
        Admin_Form_Abstract::addDates($this, ['begin', 'begin2', 'end', 'end2']);
        $this->addElement('checkbox', 'birth', [
            'label' => $this->getView()->ucstring('birth'),
            'checkedValue' => 1,
            'uncheckedValue' => 0,
        ]);
        $this->addElement('checkbox', 'death', [
            'label' => $this->getView()->ucstring('death'),
            'checkedValue' => 1,
            'uncheckedValue' => 0,
        ]);
        $this->addElement('text', 'name', [
            'class' => 'required',
            'required' => true,
            'label' => $this->getView()->ucstring('name'),
        ]);
        $this->addElement('textarea', 'description', ['label' => $this->getView()->ucstring('description')]);
        $this->addElement('hidden', 'genderId', ['decorators' => ['ViewHelper']]);
        $this->addElement('text', 'genderButton', [
            'label' => 'Gender',
            'class' => 'tableSelect',
            'readonly' => true,
            'onfocus' => 'this.blur()',
            'placeholder' => $this->getView()->ucstring('select'),
            'attribs' => ['readonly' => 'true'],
        ]);
        $this->addElement('hidden', 'residenceId', ['decorators' => ['ViewHelper']]);
        $this->addElement('text', 'residenceButton', [
            'label' => $this->getView()->ucstring('residence'),
            'class' => 'tableSelect',
            'readonly' => true,
            'onfocus' => 'this.blur()',
            'placeholder' => $this->getView()->ucstring('select'),
            'attribs' => ['readonly' => 'true'],
        ]);
        $this->addElement('hidden', 'appearsFirstId', ['decorators' => ['ViewHelper']]);
        $this->addElement('text', 'appearsFirstButton', [
            'label' => $this->getView()->ucstring('appears_first'),
            'class' => 'tableSelect',
            'readonly' => true,
            'onfocus' => 'this.blur()',
            'placeholder' => $this->getView()->ucstring('select'),
            'attribs' => ['readonly' => 'true'],
        ]);
        $this->addElement('hidden', 'appearsLastId', ['decorators' => ['ViewHelper']]);
        $this->addElement('text', 'appearsLastButton', [
            'label' => $this->getView()->ucstring('appears_last'),
            'class' => 'tableSelect',
            'readonly' => true,
            'onfocus' => 'this.blur()',
            'placeholder' => $this->getView()->ucstring('select'),
            'attribs' => ['readonly' => 'true'],
        ]);
        foreach (['alias'] as $field) {
            $this->addElement('button', $field . 'ElementAdd', ['label' => '+']);
            $this->addElement('hidden', $field . 'ElementId', ['value' => 1]);
        }
        $submitLabel = 'save';
        if (Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'insert') {
            $submitLabel = 'insert';
        }
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring($submitLabel), 'type' => 'submit']);
        $this->addElement('hidden', 'continue', ['decorators' => ['ViewHelper'], 'value' => 0]);
        $this->addElement('button', 'continueButton', [
            'label' => $this->getView()->ucstring('insert_and_continue'),
            'type' => 'submit',
            'onclick' => "$('#continue').val(1);$('#actorForm').submit();return false;"
        ]);
        $this->setElementFilters(['StringTrim']);
    }

}
