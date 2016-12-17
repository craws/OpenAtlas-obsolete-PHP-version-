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
                <a href="/admin/' . $controller . '/view/id/' . $pagerIds['first_id'] . '">
                    <div class="navigation first disabled" tabindex="0" aria-disabled="true"></div>
                </a>
                <a href="/admin/' . $controller . '/view/id/' . $pagerIds['previous_id'] . '">
                    <div class="navigation prev disabled" tabindex="0" aria-disabled="true"></div>
                </a>';
        }
        if ($pagerIds['last_id'] != $entity->id) {
            $html .= '
                <a href="/admin/' . $controller . '/view/id/' . $pagerIds['next_id'] . '">
                    <div class="navigation next" tabindex="0" aria-disabled="false"></div>
                </a>
                <a href="/admin/' . $controller . '/view/id/' . $pagerIds['last_id'] . '">
                    <div class="navigation last" tabindex="0" aria-disabled="false"></div>
                </a> ';
        }
        $returnHtml = ($html) ? '<div class="pager" style="float:left;margin-right:0.5em;margin-top:0.1em;">' . $html .
            '</div>' : '';
        return $returnHtml;
    }

}
