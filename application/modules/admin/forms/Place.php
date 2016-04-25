<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Place extends Craws\Form\Table {

    public function init() {
        $this->setName('placeForm')->setMethod('post');
        $this->setAction($this->getView()->url());
        $this->addElement('text', 'name', [
            'class' => 'required',
            'required' => true,
            'label' => $this->getView()->ucstring('name'),
        ]);
        $this->addElement('hidden', 'siteId', ['decorators' => ['ViewHelper']]);
        $this->addElement('text', 'siteButton', [
            'label' => $this->getView()->ucstring('site'),
            'required' => true,
            'class' => 'tableSelect required',
            'readonly' => true,
            'onfocus' => 'this.blur()',
            'placeholder' => $this->getView()->ucstring('select'),
            'attribs' => ['readonly' => 'true'],
        ]);
        $this->addElement('hidden', 'administrativeId', ['decorators' => ['ViewHelper']]);
        $this->addElement('hidden', 'historicalId', ['decorators' => ['ViewHelper']]);
        $this->addElement('button', 'aliasAdd', ['label' => '+']);
        $this->addElement('hidden', 'aliasId', ['value' => 1]);
        Admin_Form_Abstract::addDates($this, ['begin', 'begin2', 'end', 'end2']);
        $this->addElement('text', 'easting', ['label' => $this->getView()->ucstring('easting')]);
        $this->addElement('text', 'northing', ['label' => $this->getView()->ucstring('northing')]);
        $this->addElement('textarea', 'description', ['label' => $this->getView()->ucstring('description')]);
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
            'onclick' => "$('#continue').val(1);$('#placeForm').submit();return false;"
        ]);
        $this->setElementFilters(['StringTrim']);
    }

}
