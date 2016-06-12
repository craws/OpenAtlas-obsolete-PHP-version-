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
        $hierarchies = $form->addHierarchies($rootType->name);
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->form = $form;
            return;
        }
        $reference = Model_EntityMapper::insert('E31', $form->getValue('name'), $form->getValue('description'));
        self::save($form, $reference, $hierarchies);
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
        // find out if has a bibliography or edition type
        $referenceRootType = null;
        $referenceType = null;
        $types = Model_LinkMapper::getLinkedEntities($reference, 'P2');
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
        $hierarchies = $form->addHierarchies($rootType->name, $reference);
        $this->view->form = $form;
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
        foreach (Model_LinkMapper::getLinks($reference, ['P2']) as $link) {
            $link->delete();
        }
        self::save($form, $reference, $hierarchies);
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

    private function save(Zend_Form $form, Model_Entity $entity, array $hierarchies) {
        foreach ($hierarchies as $hierarchy) {
            $idField = $hierarchy->nameClean . 'Id';
            if ($form->getValue($idField)) {
                foreach (explode(",", $form->getValue($idField)) as $id) {
                    Model_LinkMapper::insert('P2', $entity, Model_NodeMapper::getById($id));
                }
            } else if ($hierarchy->system) {
                Model_LinkMapper::insert('P2', $entity, $hierarchy);
            }
        }
    }
}
