<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Content extends Craws\Form\Table {

    public function init() {
        $this->setName('contentForm')->setMethod('post');
        foreach (Model_LanguageMapper::getAll() as $language) {
            $subform = new \Craws\Form\TableSubForm();
            $subform->setDescription($language->name);
            $subform->addElement('textarea', 'text', [
                'label' => 'text',
                'class' => 'tinymce',
                'rows' => 4,
                'cols' => 50
            ]);
            $this->addSubForm($subform, $language->shortform);
        }
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring('save'), 'type' => 'submit']);
        $this->setElementFilters(['StringTrim']);
    }

}
