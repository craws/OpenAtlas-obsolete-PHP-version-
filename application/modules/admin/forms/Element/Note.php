<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

/* helper class to show text in forms, e.g. selected entries of multitype */
class Admin_Form_Element_Note extends Zend_Form_Element_Xhtml {
    public $helper = 'formNote';
}
