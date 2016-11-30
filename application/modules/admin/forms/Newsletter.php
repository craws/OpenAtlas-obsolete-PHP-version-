<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Newsletter extends Craws\Form\Table {

    public function init() {
        $this->setAction($this->getView()->url());
        $this->setName('newsletterForm')->setMethod('post');
        $this->addElement('text', 'subject', [
            'label' => $this->getView()->ucstring('subject'),
            'required' => true,
            'class' => 'required',
            'style' => 'width:28em;',
            'placeholder' => $this->getView()->ucstring('subject'),
        ]);
        $this->addElement('textarea', 'body', [
            'label' => $this->getView()->ucstring('info'),
            'required' => true,
            'class' => 'required',
            'style' => 'width:32em;height:14em;',
            'placeholder' => $this->getView()->ucstring('content'),
        ]);
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring('send'), 'type' => 'submit']);
        $this->setElementFilters(['StringTrim']);
    }

}
