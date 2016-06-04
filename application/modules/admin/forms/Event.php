<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Event extends Admin_Form_Base {

    public function init() {
        $this->setName('eventForm')->setMethod('post');
        $this->setAction($this->getView()->url());
        $this->addDates(['begin', 'begin2', 'end', 'end2']);
        $this->addElement('text', 'name', [
            'class' => 'required',
            'required' => true,
            'label' => $this->getView()->ucstring('name'),
        ]);
        $this->addElement('textarea', 'description', ['label' => $this->getView()->ucstring('description')]);
        $this->addElement('hidden', 'superId', ['decorators' => ['ViewHelper']]);
        $this->addElement('text', 'superButton', [
            'label' => $this->getView()->ucstring('super'),
            'class' => 'tableSelect',
            'readonly' => true,
            'onfocus' => 'this.blur()',
            'placeholder' => $this->getView()->ucstring('select'),
            'attribs' => ['readonly' => 'true'],
        ]);
        $this->addElement('hidden', 'placeId', ['decorators' => ['ViewHelper']]);
        $this->addElement('text', 'placeButton', [
            'label' => $this->getView()->ucstring('place'),
            'class' => 'tableSelect',
            'readonly' => true,
            'onfocus' => 'this.blur()',
            'placeholder' => $this->getView()->ucstring('select'),
            'attribs' => ['readonly' => 'true'],
        ]);
        $this->addElement('hidden', 'typeId', ['decorators' => ['ViewHelper']]);
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
            'onclick' => "$('#continue').val(1);$('#eventForm').submit();return false;"
        ]);
        $this->setElementFilters(['StringTrim']);
    }

    public function addFields($class) {
        if ($class->name == 'Acquisition') {
            $this->addElement('hidden', 'recipientId', ['decorators' => ['ViewHelper']]);
            $this->addElement('text', 'recipientButton', [
                'label' => $this->getView()->ucstring('recipient'),
                'class' => 'tableSelect',
                'readonly' => true,
                'onfocus' => 'this.blur()',
                'placeholder' => $this->getView()->ucstring('select'),
                'attribs' => ['readonly' => 'true'],
            ]);
            $this->addElement('hidden', 'donorId', ['decorators' => ['ViewHelper']]);
            $this->addElement('text', 'donorButton', [
                'label' => $this->getView()->ucstring('donor'),
                'class' => 'tableSelect',
                'readonly' => true,
                'onfocus' => 'this.blur()',
                'placeholder' => $this->getView()->ucstring('select'),
                'attribs' => ['readonly' => 'true'],
            ]);
            $this->addElement('hidden', 'acquisitionPlaceId', ['decorators' => ['ViewHelper']]);
            $this->addElement('text', 'acquisitionPlaceButton', [
                'label' => $this->getView()->ucstring('given_place'),
                'class' => 'tableSelect',
                'readonly' => true,
                'onfocus' => 'this.blur()',
                'placeholder' => $this->getView()->ucstring('select'),
                'attribs' => ['readonly' => 'true'],
            ]);
        }
    }

}
