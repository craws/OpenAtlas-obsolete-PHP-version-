<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Profile extends Craws\Form\Table {

    public function init() {
        $this->setName('profileForm')->setMethod('post');
        $this->addElement('text', 'realName', [
            'label' => $this->getView()->ucstring('name'),
            'validators' => [['StringLength', false, [1, 32]]],
        ]);
        $this->addElement('text', 'email', [
            'label' => $this->getView()->ucstring('email'),
            'filters' => ['StringToLower'],
            'validators' => [['EmailAddress']]
        ]);
        $this->addElement('checkbox', 'show_email', [
            'label' => $this->getView()->ucstring('show_email'),
            'checkedValue' => 1,
            'uncheckedValue' => 0,
            'value' => 0,
        ]);
        $this->addElement('checkbox', 'newsletter', [
            'label' => $this->getView()->ucstring('newsletter'),
            'checkedValue' => 1,
            'uncheckedValue' => 0,
            'value' => 0,
        ]);
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring('save'), 'type' => 'submit']);
        $this->setElementFilters(['StringTrim']);
    }

}
