<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

/* this is a viewless controller for generic calls, e.g. ajax actions */

class Admin_FunctionController extends Zend_Controller_Action {

    public function addFieldAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $element = new \Zend_Form_Element_Text($this->_getParam('name') . $this->_getParam('elementId'));
        $element->setBelongsTo($this->_getParam('name'));
        echo '<br/>' . $element->renderViewHelper();
    }

    public function bookmarkAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender(true);
        $label = Model_UserMapper::bookmark($this->_getParam('entityId'));
        echo $this->view->ucstring($label);
    }

    public function unlinkAction() {
        Model_LinkMapper::getById($this->_getParam('id'))->delete();
        $this->_helper->message('info_delete');
        $array = Zend_Registry::get('config')->get('codeView')->toArray();
        $entity = Model_EntityMapper::getById($this->_getParam('entityId'));
        $url = '/admin/' . $array[$entity->class->code] . '/view/id/' . $entity->id . '/';
        return $this->_helper->redirector->gotoUrl($url);
    }

}
