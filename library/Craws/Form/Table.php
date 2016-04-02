<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

namespace Craws\Form;

class Table extends Decorative {

    public $decorators = [
        'ZendX_JQuery_Form_Element_DatePicker' => [
            'UiWidgetElement',
            [['data' => 'HtmlTag'], ['tag' => 'div', 'class' => 'tableCell']],
            ['Label', ['tag' => 'div', 'class' => 'tableCell']],
            [['row' => 'HtmlTag'], ['tag' => 'div', 'class' => 'tableRow']],
        ],
        'Zend_Form_Element_Submit' => [
            'ViewHelper',
            [['data' => 'HtmlTag'], ['tag' => 'div', 'class' => 'tableSubmit']],
            [['row' => 'HtmlTag'], ['tag' => 'div', 'class' => 'tableRow']],
        ],
        'Zend_Form_Element' => [
            'ViewHelper',
            'Errors',
            [['data' => 'HtmlTag'], ['tag' => 'div', 'class' => 'tableCell']],
            ['Label', ['tag' => 'div', 'requiredSuffix' => ' *']],
            [['row' => 'HtmlTag'], ['tag' => 'div', 'class' => 'tableRow']],
        ],
        'Zend_Form' => [
            'FormErrors',
            'FormElements',
            ['HtmlTag', ['tag' => 'div']],
            'Form'
        ],
    ];

}
