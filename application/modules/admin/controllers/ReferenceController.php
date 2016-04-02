<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_ReferenceController extends Zend_Controller_Action {

    public function indexAction() {
        $this->view->references = Model_EntityMapper::getByCodes('Reference');
    }

    public function insertAction() {
        $form = new Admin_Form_Reference();
        $rootType = $this->_getParam('type');
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->form = $form;
            $this->view->rootType = $rootType;
            $this->view->typeTreeData = Model_NodeMapper::getTreeData('type', $rootType);
            return;
        }
        $reference = Model_EntityMapper::insert('E31', $form->getValue('name'), $form->getValue('description'));
        Model_LinkMapper::insert('P2', $reference, Model_EntityMapper::getById($this->_getParam('typeId')));
        $this->_helper->message('info_insert');
        // @codeCoverageIgnoreStart
        if ($form->getElement('continue')->getValue()) {
            return $this->_helper->redirector->gotoUrl('/admin/reference/insert/type/' . $rootType);
        }
        // @codeCoverageIgnoreEnd
        return $this->_helper->redirector->gotoUrl('/admin/reference/view/id/' . $reference->id);
    }

    public function updateAction() {
        $reference = Model_EntityMapper::getById($this->_getParam('id'));
        $type = Model_LinkMapper::getLinkedEntity($reference, 'P2');
        $rootType = Model_EntityMapper::getById($type->rootId);
        $form = new Admin_Form_Reference();
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $form->populate([
                'name' => $reference->name,
                'description' => $reference->description,
                'typeId' => $type->id,
                'typeButton' => $type->name
            ]);
            $this->view->form = $form;
            $this->view->reference = $reference;
            $this->view->typeTreeData = Model_NodeMapper::getTreeData('type', $rootType->name, $type);
            return;
        }
        $reference->name = $form->getValue('name');
        $reference->description = $form->getValue('description');
        $reference->update();
        Model_LinkMapper::getLink($reference, 'P2')->delete();
        Model_LinkMapper::insert('P2', $reference, Model_EntityMapper::getById($this->_getParam('typeId')));
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/reference/view/id/' . $reference->id);
    }

    public function viewAction() {
        $reference = Model_EntityMapper::getById($this->_getParam('id'));
        $type = Model_LinkMapper::getLinkedEntity($reference, 'P2');
        $typeRoot = Model_NodeMapper::getById($type->rootId);
        $actorLinks = [];
        $sourceLinks = [];
        $eventLinks = [];
        $placeLinks = [];
        foreach (Model_LinkMapper::getLinks($reference, 'P67') as $link) {
            $code = $link->getRange()->getClass()->code;
            if ($code == 'E33') {
                $sourceLinks[] = $link;
            } else if ($code == 'E18') {
                $placeLinks[] = $link;
            } else if (in_array($code, Zend_Registry::get('config')->get('codeEvent')->toArray())) {
                $eventLinks[] = $link;
            } else if (in_array($code, Zend_Registry::get('config')->get('codeActor')->toArray())) {
                $actorLinks[] = $link;
            }
        }
        $this->view->reference = $reference;
        $this->view->referenceType = Model_NodeMapper::getNodeByEntity('type', $typeRoot->name, $reference);
        $this->view->actorLinks = $actorLinks;
        $this->view->sourceLinks = $sourceLinks;
        $this->view->eventLinks = $eventLinks;
        $this->view->placeLinks = $placeLinks;
    }

    public function deleteAction() {
        Model_EntityMapper::getById($this->_getParam('id'))->delete();
        $this->_helper->message('info_delete');
        return $this->_helper->redirector->gotoUrl('/admin/reference');
    }

}
