<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

namespace Craws\Form;

class TableSubForm extends DecorativeSubForm {

    public $decorators = [
        'Zend_Form_Label' => [
            'ViewHelper',
            'Errors',
            [['data' => 'HtmlTag'], ['tag' => 'div', 'class' => 'form']]
        ],
        'Zend_Form_Element' => [
            'ViewHelper',
            'Errors',
            [['data' => 'HtmlTag'], ['tag' => 'div', 'class' => 'tableCell']],
            ['Label', ['tag' => 'div', 'requiredSuffix' => ' *']],
            [['row' => 'HtmlTag'], ['tag' => 'div', 'class' => 'tableRow']],
        ],
        'Zend_Form' => ['Description', 'FormElements', [['row' => 'HtmlTag'], ['tag' => 'div']]]
    ];

}
