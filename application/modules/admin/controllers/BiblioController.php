<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_BiblioController extends Zend_Controller_Action {

    public function insertAction() {
        $entity = Model_EntityMapper::getById($this->_getParam('id'));
        $array = Zend_Registry::get('config')->get('codeView')->toArray();
        $controller = $array[$entity->class->code];
        $form = new Admin_Form_Biblio();
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->controller = $controller;
            $this->view->entity = $entity;
            $this->view->form = $form;
            $this->view->menuHighlight = $controller;
            $this->view->references = Model_EntityMapper::getByCodes('Bibliography');
            if ($entity->class->code == 'E33') {
                $this->view->references = Model_EntityMapper::getByCodes('Reference');
            }
            return;
        }
        $reference = Model_EntityMapper::getById($this->_getParam('referenceId'));
        $propertyCode = ($reference->class->code == 'E84') ? 'P128' : 'P67';
        Model_LinkMapper::insert($propertyCode, $reference, $entity, $form->getValue('page'));
        $this->_helper->message('info_insert');
        return $this->_helper->redirector->gotoUrl('/admin/' . $controller . '/view/id/' . $entity->id . '/#tabReference');
    }

    public function updateAction() {
        $link = Model_LinkMapper::getById($this->_getParam('id'));
        $entity = $link->range;
        $reference = $link->domain;
        $array = Zend_Registry::get('config')->get('codeView')->toArray();
        $controller = $array[$entity->class->code];
        $tab = 'Reference';
        $this->view->object = $link->range;
        $this->view->relatedObject = $reference;
        if ($this->_getParam('origin') == 'reference') {
            $controller = ($reference->class->code == 'E84') ? 'carrier' : 'reference';
            $tab = ucfirst($controller);
            $this->view->object = $reference;
            $this->view->relatedObject = $link->range;
        }
        $form = new Admin_Form_Biblio();
        $form->removeElement('referenceButton');
        $form->removeElement('referenceId');
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $form->populate(['page' => $link->description]);
            $this->view->controller = $controller;
            $this->view->entity = $entity;
            $this->view->form = $form;
            $this->view->menuHighlight = $controller;
            $this->view->reference = $reference;
            $this->view->tab = $tab;
            return;
        }
        $link->description = $form->getValue('page');
        $link->update();
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/' . $controller . '/view/id/' . $this->view->object->id . '/#tab' . $tab);
    }

}
