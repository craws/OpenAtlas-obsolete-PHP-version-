<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_ClassController extends Zend_Controller_Action {

    public function init() {
        $this->view->menuHighlight = 'overview';
    }

    public function indexAction() {
        $this->view->classes = Zend_Registry::get('classes');
    }

    public function viewAction() {
        $classes = Zend_Registry::get('classes');
        $class = $classes[$this->_getParam('id')];
        $this->view->class = $class;
        $domains = [];
        $ranges = [];
        foreach (Zend_Registry::get('properties') as $property) {
            if ($class->id == $property->getDomain()->id) {
                $domains[] = $property;
            } else if ($class->id == $property->getRange()->id) {
                $ranges[] = $property;
            }
        }
        $this->view->domains = $domains;
        $this->view->ranges = $ranges;
    }

}
