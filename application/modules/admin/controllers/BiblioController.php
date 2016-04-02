<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_BiblioController extends Zend_Controller_Action {

    public function insertAction() {
        $entity = Model_EntityMapper::getById($this->_getParam('id'));
        $array = Zend_Registry::get('config')->get('codeView')->toArray();
        $controller = $array[$entity->getClass()->code];
        $form = new Admin_Form_Biblio();
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->menuHighlight = $controller;
            $this->view->entity = $entity;
            $this->view->controller = $controller;
            $this->view->form = $form;
            $this->view->references = Model_EntityMapper::getByCodes('Bibliography');
            if ($entity->getClass()->code == 'E33') {
                $this->view->references = Model_EntityMapper::getByCodes('Reference');
            }
            return;
        }
        $reference = Model_EntityMapper::getById($this->_getParam('referenceId'));
        $propertyCode = 'P67';
        if ($reference->getClass()->code == 'E84') {
            $propertyCode = 'P128';
        }
        Model_LinkMapper::insert($propertyCode, $reference, $entity, $form->getValue('page'));
        $this->_helper->message('info_insert');
        return $this->_helper->redirector->gotoUrl('/admin/' . $controller . '/view/id/' . $entity->id . '/#tabReference');
    }

    public function updateAction() {
        $link = Model_LinkMapper::getById($this->_getParam('id'));
        $entity = $link->getRange();
        $reference = $link->getDomain();
        $array = Zend_Registry::get('config')->get('codeView')->toArray();
        $controller = $array[$entity->getClass()->code];
        if ($this->_getParam('origin') == 'reference') {
            $tab = ucfirst($controller);
            $controller = 'reference';
            if ($reference->getClass()->code == 'E84') {
                $controller = 'carrier';
            }
            $this->view->object = $reference;
            $this->view->relatedObject = $link->getRange();
        } else {
            $tab = 'Reference';
            $this->view->object = $link->getRange();
            $this->view->relatedObject = $reference;
        }
        $form = new Admin_Form_Biblio();
        $form->removeElement('referenceButton');
        $form->removeElement('referenceId');
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $form->populate(['page' => $link->description]);
            $this->view->menuHighlight = $controller;
            $this->view->entity = $entity;
            $this->view->reference = $reference;
            $this->view->controller = $controller;
            $this->view->form = $form;
            $this->view->tab = $tab;
            return;
        }
        $link->description = $form->getValue('page');
        $link->update();
        $this->_helper->message('info_update');
        // @codeCoverageIgnoreStart
        return $this->_helper->redirector->gotoUrl('/admin/' . $controller . '/view/id/' . $this->view->object->id . '/#tab' . $tab);
        // @codeCoverageIgnoreEnd
    }

}
