<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Feedback extends Craws\Form\Table {

    public function init() {
        $this->setName('feedbackForm');
        $this->setMethod('post');
        $this->getView()->setEscape('stripslashes');
        $subject = $this->createElement('select', 'subject', ['class' => 'required']);
        $subject->setLabel($this->getView()->ucstring('subject'))->addMultiOptions([
            'suggestion' => $this->getView()->ucstring('suggestion'),
            'question' => $this->getView()->ucstring('question'),
            'problem' => $this->getView()->ucstring('problem'),
        ]);
        $this->addElement($subject);
        $this->addElement('textarea', 'description', [
            'label' => $this->getView()->ucstring('description'),
            'style' => 'width:40em;height:20em;',
            'class' => 'required',
            'required' => true
        ]);
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring('send'), 'type' => 'submit']);
        $this->setElementFilters(['StringTrim']);
    }

}
