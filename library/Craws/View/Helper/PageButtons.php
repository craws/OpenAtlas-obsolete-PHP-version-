<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_PageButtons extends Zend_View_Helper_Abstract {

    public function pageButtons(Model_Entity $entity) {
        $classCodes = [];
        if (in_array($entity->class->code, Zend_Registry::get('config')->get('codeEvent')->toArray())) {
            $classCodes = Zend_Registry::get('config')->get('codeEvent')->toArray();
        } else if (in_array($entity->class->code, Zend_Registry::get('config')->get('codeActor')->toArray())) {
            $classCodes = Zend_Registry::get('config')->get('codeActor')->toArray();
        } else if (in_array($entity->class->code, Zend_Registry::get('config')->get('codeSource')->toArray())) {
            $classCodes = Zend_Registry::get('config')->get('codeSource')->toArray();
        } else if (in_array($entity->class->code, Zend_Registry::get('config')->get('codePhysicalObject')->toArray())) {
            $classCodes = Zend_Registry::get('config')->get('codePhysicalObject')->toArray();
        } else if (in_array($entity->class->code, ['E84', 'E31'])) {
            $classCodes = ['E84', 'E31'];
        }
        $html = '';
        $controller = Zend_Controller_Front::getInstance()->getRequest()->getParam('controller');
        $previous_id = Model_EntityMapper::get_previous_id($entity, $classCodes);
        if ($previous_id) {
            $html .= '<a class="button" href="/admin/' . $controller . '/view/id/' . $previous_id . '"><</a> ';
        }
        $next_id = Model_EntityMapper::get_next_id($entity, $classCodes);
        if ($next_id) {
            $html .= '<a class="button" href="/admin/' . $controller . '/view/id/' . $next_id . '">></a> ';
        }
        return $html;
    }

}
