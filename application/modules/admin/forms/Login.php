<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Login extends Craws\Form\Table {

    public function init() {
        $this->setName('loginForm')->setMethod('post');
        $username = new Zend_Form_Element_Text('username', [
            'label' => $this->getView()->ucstring('username'),
            'filters' => ['StringToLower'],
            'validators' => [['StringLength', false, [1, 64]]],
            'required' => true,
            'class' => 'required'
        ]);
        $this->addElement($username);
        $username->getDecorator('Label')->setOption('requiredSuffix', '');
        $password = new Zend_Form_Element_Password('password', [
            'label' => $this->getView()->ucstring('password'),
            'validators' => [['StringLength', false, [1, 64]]],
            'required' => true,
            'class' => 'required'
        ]);
        $this->addElement($password);
        $password->getDecorator('Label')->setOption('requiredSuffix', '');
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring('login'), 'type' => 'submit']);
        $this->setElementFilters(['StringTrim']);
    }

}
