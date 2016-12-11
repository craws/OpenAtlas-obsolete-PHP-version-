<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Node extends Admin_Form_Base {

    public function init() {
        $this->setAction($this->getView()->url());
        $this->setName('nodeForm')->setMethod('post');
        $this->addElement('text', 'name', [
            'label' => $this->getView()->ucstring('name'),
            'required' => true,
            'class' => 'required',
        ]);
        $this->addElement('text', 'inverse_text', ['label' => $this->getView()->ucstring('inverse')]);
        $this->addElement('textarea', 'description', ['label' => $this->getView()->ucstring('description')]);
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring('save'), 'type' => 'submit']);
        $this->setElementFilters(['StringTrim']);
    }

}
