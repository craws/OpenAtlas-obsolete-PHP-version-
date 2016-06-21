<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Text extends Craws\Form\Table {

    public function init() {
        $this->setName('textForm')->setMethod('post');
        $this->setAction($this->getView()->url());
        $typeElement = $this->createElement('select', 'type', ['required' => true, 'class' => 'required']);
        $typeElement->setLabel($this->getView()->ucstring('type'));
        $types = Model_NodeMapper::getOptionsForSelect('linguistic object classification');
        if (($key = array_search('Source Content', $types)) !== false) {
            unset($types[$key]);
        }
        $typeElement->addMultiOptions(['' => html_entity_decode('&nbsp;')]);
        $typeElement->addMultiOptions($types);
        $this->addElement($typeElement);
        $this->addElement('text', 'name', [
            'label' => $this->getView()->ucstring('name'),
            'class' => 'required',
            'required' => true,
        ]);
        $this->addElement('textarea', 'description', ['label' => $this->getView()->ucstring('text')]);
        $submitLabel = 'save';
        if (Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'text-add') {
            $submitLabel = 'insert';
        }
        $this->addElement('button', 'formSubmit', [
            'label' => $this->getView()->ucstring($submitLabel),
            'type' => 'submit',
        ]);
        $this->setElementFilters(['StringTrim']);
    }

}
