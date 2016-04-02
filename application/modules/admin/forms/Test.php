<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Test extends Craws\Form\Table {

    public function init() {
        $this->setName('textForm')->setMethod('post');
        $this->setAction($this->getView()->url());
        $domain = $this->createElement('select', 'domain', ['required' => true, 'class' => 'required']);
        $domain->setLabel($this->getView()->ucstring('domain'));
        $domain->addMultiOptions(['' => html_entity_decode('&nbsp;')]);
        foreach (Zend_Registry::get('classes') as $class) {
            $domain->addMultiOptions([$class->id => $class->code]);
        }
        $this->addElement($domain);
        $property = $this->createElement('select', 'property', ['required' => true, 'class' => 'required']);
        $property->setLabel($this->getView()->ucstring('property'));
        $property->addMultiOptions(['' => html_entity_decode('&nbsp;')]);
        foreach (Zend_Registry::get('properties') as $item) {
            $property->addMultiOptions([$item->id => $item->code]);
        }
        $this->addElement($property);
        $range = $this->createElement('select', 'range', ['required' => true, 'class' => 'required']);
        $range->setLabel($this->getView()->ucstring('range'));
        $range->addMultiOptions(['' => html_entity_decode('&nbsp;')]);
        foreach (Zend_Registry::get('classes') as $class) {
            $range->addMultiOptions([$class->id => $class->code]);
        }
        $this->addElement($range);
        $this->addElement('button', 'formSubmit', [
            'label' => $this->getView()->ucstring('test'),
            'type' => 'submit',
        ]);
        $this->setElementFilters(['StringTrim']);
    }

}
