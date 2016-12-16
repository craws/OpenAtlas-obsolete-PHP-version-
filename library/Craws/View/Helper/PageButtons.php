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
        $pagerIds = Model_EntityMapper::getPagerIds($entity, $classCodes);
        if ($pagerIds['first_id'] != $entity->id) {
            $html .= '
                <a class="button list-pager" href="/admin/' . $controller . '/view/id/' . $pagerIds['first_id'] . '">|<</a>
                <a class="button list-pager" href="/admin/' . $controller . '/view/id/' . $pagerIds['previous_id'] . '"><</a> ';
        }
        if ($pagerIds['last_id'] != $entity->id) {
            $html .= '
                <a class="button list-pager" href="/admin/' . $controller . '/view/id/' . $pagerIds['next_id'] . '">></a>
                <a class="button list-pager" href="/admin/' . $controller . '/view/id/' . $pagerIds['last_id'] . '">>|</a> ';
        }
        $returnHtml = ($html) ? '<span style="margin-right:1em;">' . $html . '</span>' : '';
        return $returnHtml;
    }

}
