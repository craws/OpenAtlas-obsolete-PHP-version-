<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Biblio extends Craws\Form\Table {

    public function init() {
        $this->setName('biblioForm')->setMethod('post');
        $this->addElement('hidden', 'referenceId', ['decorators' => ['ViewHelper']]);
        $this->addElement('text', 'referenceButton', [
            'required' => 'true',
            'label' => $this->getView()->ucstring('reference'),
            'class' => 'required tableSelect',
            'readonly' => true,
            'onfocus' => 'this.blur()',
            'placeholder' => $this->getView()->ucstring('select'),
            'attribs' => ['readonly' => 'true'],
        ]);
        $this->addElement('text', 'page', ['label' => $this->getView()->ucstring('page(s)')]);
        $submitLabel = 'save';
        if (Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'insert') {
            $submitLabel = 'insert';
        }
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring($submitLabel), 'type' => 'submit']);
        $this->setElementFilters(['StringTrim']);
    }

}
