<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_ReferenceController extends Zend_Controller_Action {

    public function deleteAction() {
        Model_EntityMapper::getById($this->_getParam('id'))->delete();
        $this->_helper->message('info_delete');
        return $this->_helper->redirector->gotoUrl('/admin/reference');
    }

    public function indexAction() {
        $this->view->references = Model_EntityMapper::getByCodes('Reference');
    }

    public function insertAction() {
        $form = new Admin_Form_Reference();
        $rootType = Model_NodeMapper::getRootType($this->_getParam('type'));
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->form = $form;
            $this->view->rootType = $rootType->name;
            $this->view->typeTreeData = Model_NodeMapper::getTreeData($rootType->name);
            return;
        }
        $reference = Model_EntityMapper::insert('E31', $form->getValue('name'), $form->getValue('description'));
        $typeId = ($this->_getParam('typeId')) ? $this->_getParam('typeId') : $rootType->id;
        Model_LinkMapper::insert('P2', $reference, Model_NodeMapper::getById($typeId));
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
        $this->view->reference = $reference;
        $form = new Admin_Form_Reference();
        $this->view->form = $form;
        $types = Model_LinkMapper::getLinkedEntities($reference, 'P2');
        // find out if has a bibliography or edition type
        $referenceRootType = null;
        $referenceType = null;
        foreach ($types as $type) {
            if (!$type->system) {
                continue;
            }
            $rootType = ($type->rootId) ? Model_NodeMapper::getById($type->rootId) : $type;
            switch ($rootType->name) {
                case 'Bibliography':
                case 'Edition':
                    $referenceType = $type;
                    $referenceRootType = $rootType;

                    break 2;
            }
        }
        if (!$this->getRequest()->isPost()) {
            $form->populate([
                'name' => $reference->name,
                'description' => $reference->description,
                'typeId' => $referenceType->id,
                'modified' => ($reference->modified) ? $reference->modified->getTimestamp() : 0
            ]);
            if ($referenceType->rootId) {
                $form->populate(['typeButton' => $referenceType->name]);
            }
            $this->view->typeTreeData = Model_NodeMapper::getTreeData($referenceRootType->name, $referenceType);
            return;
        }
        $formValid = $form->isValid($this->getRequest()->getPost());
        $modified = Model_EntityMapper::checkIfModified($reference, $form->modified->getValue());
        // @codeCoverageIgnoreStart
        if ($modified) {
            $log = Model_UserLogMapper::getLogForView('entity', $reference->id);
            $this->view->modifier = $log['modifier_name'];
        }
        // @codeCoverageIgnoreEnd
        if (!$formValid || $modified) {
            $this->view->typeTreeData = Model_NodeMapper::getTreeData($referenceRootType->name);
            $this->_helper->message('error_modified');
            return;
        }
        $reference->name = $form->getValue('name');
        $reference->description = $form->getValue('description');
        $reference->update();
        Model_LinkMapper::getLink($reference, 'P2')->delete();
        $type = ($this->_getParam('typeId')) ? Model_NodeMapper::getById($this->_getParam('typeId')) : $referenceRootType;
        Model_LinkMapper::insert('P2', $reference, $type);
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/reference/view/id/' . $reference->id);
    }

    public function viewAction() {
        $reference = Model_EntityMapper::getById($this->_getParam('id'));
        $actorLinks = [];
        $sourceLinks = [];
        $eventLinks = [];
        $placeLinks = [];
        foreach (Model_LinkMapper::getLinks($reference, 'P67') as $link) {
            $code = $link->range->class->code;
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
        $this->view->referenceType = Model_LinkMapper::getLinkedEntity($reference, 'P2');
        $this->view->actorLinks = $actorLinks;
        $this->view->sourceLinks = $sourceLinks;
        $this->view->eventLinks = $eventLinks;
        $this->view->placeLinks = $placeLinks;
    }

}
