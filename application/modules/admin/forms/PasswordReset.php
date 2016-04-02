<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_PasswordReset extends Craws\Form\Table {

    // @codeCoverageIgnoreStart
    public function init() {
        $this->setName('passwordResetForm');
        $this->setMethod('post');
        $email = new Zend_Form_Element_Text('email', [
            'label' => $this->getView()->ucstring('email'),
            'filters' => ['StringToLower'],
            'validators' => [['EmailAddress']],
            'required' => true,
            'class' => 'required',
        ]);
        $this->addElement($email);
        $email->getDecorator('Label')->setOption('requiredSuffix', '');
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring('password_reset'), 'type' => 'submit']);
        $this->setElementFilters(['StringTrim']);
    }

    // @codeCoverageIgnoreEnd
}
