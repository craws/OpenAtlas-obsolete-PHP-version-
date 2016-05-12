<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_SearchController extends Zend_Controller_Action {

    public function indexAction() {
        $form = new Admin_Form_Search();
        $term = trim($this->_getParam('term'));
        $classes = $this->_getParam('class');
        $own = $this->_getParam('searchOwn');
        $description = $this->_getParam('searchDescription');
        if (!$classes) {
            $classes = ['actor', 'reference', 'source', 'event', 'place'];
        }
        $codes = [];
        foreach ($classes as $code) {
            $codes = array_merge($codes, Zend_Registry::get('config')->get('code' . ucfirst($code))->toArray());
            switch ($code) {
                case 'actor':
                    $codes[] = 'E82'; // also search in aliases
                    break;
                case 'place':
                    if(($key = array_search('E53', $codes)) !== false) {
                        unset($codes[$key]); // remove place
                    }
                    $codes[] = 'E18'; // add objects
                    $codes[] = 'E41'; // also search in aliases
                    break;
            }
        }
        if ($term) {
            $entities = Model_EntityMapper::search($term, $codes, $description, $own);
            $this->view->entities = $entities;
        }
        $form->populate([
            'term' => $term,
            'optionToggle' => $this->_getParam('optionToggle'),
            'class' => $classes,
            'searchDescription' => $description,
            'searchOwn' => $own
        ]);
        $this->view->optionToggle = $this->_getParam('optionToggle');
        $this->view->term = $term;
        $this->view->form = $form;
    }

}
