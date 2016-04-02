<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Password extends Craws\Form\Table {

    public function init() {
        $this->setName('passwordForm')->setMethod('post');
        $this->addElement('password', 'passwordCurrent', [
            'label' => $this->getView()->ucstring('current_password'),
            'validators' => [['StringLength', false, [1, 64]]],
            'class' => 'required',
            'required' => true,
        ]);
        $this->addElement('password', 'password', [
            'validators' => [['StringLength', false, [8, 64]]],
            'class' => 'required',
            'required' => true,
            'label' => $this->getView()->ucstring('new_password')
        ]);
        $this->addElement('password', 'passwordRetype', [
            'class' => 'required',
            'required' => true,
            'label' => $this->getView()->ucstring('password_retype'),
        ]);
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring('save'), 'type' => 'submit']);
        $this->setElementFilters(['StringTrim']);
    }

}
