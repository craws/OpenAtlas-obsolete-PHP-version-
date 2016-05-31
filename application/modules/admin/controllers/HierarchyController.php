<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_HierarchyController extends Zend_Controller_Action {

    public function deleteAction() {
        $type = Model_NodeMapper::getById($this->_getParam('id'));
        if (!$type->superId || !$type->extendable) {
            $this->_helper->message('error_forbidden');
            return $this->_helper->redirector->gotoUrl('/admin/hierarchy');
        }
        if (Model_LinkMapper::getLinks($type, 'P2', true) || Model_LinkMapper::getLinks($type, 'P89', true)) {
            $this->_helper->message('error_links_exists');
            return $this->_helper->redirector->gotoUrl('/admin/hierarchy/view/id/' . $type->id);
        }
        if (!empty($type->subs)) {
            $this->_helper->message('error_subs_exists');
            return $this->_helper->redirector->gotoUrl('/admin/hierarchy/view/id/' . $type->id);
        }
        $type->delete();
        $this->_helper->message('info_delete');
        return $this->_helper->redirector->gotoUrl('/admin/hierarchy#tab' . $type->rootId);
    }

    public function deleteHierarchyAction() {

    }

    public function indexAction() {
        $nodes = [];
        foreach (Zend_Registry::get('nodes') as $node) {
            if ($node->extendable) {
                $nodes[] = $node;
            }
        }
        usort($nodes, function($a, $b) {
            return strcmp($a->name, $b->name);
        });
        $this->view->types = $nodes;
    }

    public function insertAction() {
        $super = Model_NodeMapper::getById($this->_getParam('superId'));
        if ($this->getRequest()->isPost()) {
            $name = trim(str_replace(['(', ')'], '', $this->_getParam('name')));
            if (!$name) {
                $this->_helper->message('error_name_empty');
                return $this->_helper->redirector->gotoUrl('/admin/hierarchy/#tab' . $super->rootId);
            }
            $inverse = trim(str_replace(['(', ')'], '', $this->_getParam('inverse')));
            if ($inverse) {
                $name .= ' (' . $inverse . ')';
            }
            $type = Model_EntityMapper::insert($super->class->code, $name);
            Model_LinkMapper::insert($super->propertyToSuper, $type, $super);
            $this->_helper->message('info_insert');
        }
        $tabId = ($super->rootId) ? $super->rootId : $super->id;
        return $this->_helper->redirector->gotoUrl('/admin/hierarchy/#tab' . $tabId);
    }

    public function insertHierarchyAction() {
        $form = new Admin_Form_Hierarchy();
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->form = $form;
            return;
        }
        $hierarchy = Model_EntityMapper::insert('E55', $form->getValue('name'), $form->getValue('description'));
        Model_NodeMapper::insertHierarchy($form, $hierarchy);
        $this->_helper->message('info_insert');
        return $this->_helper->redirector->gotoUrl('/admin/hierarchy/#menuTabCustom');
    }

    public function updateAction() {
        $type = Model_NodeMapper::getById($this->_getParam('id'));
        if (!$type->superId || !$type->extendable) {
            $this->_helper->message('error_forbidden');
            return $this->_helper->redirector->gotoUrl('/admin/hierarchy');
        }
        $form = new Admin_Form_Node();
        if (!$type->directional) {
            $form->removeElement('inverse');
        }
        $superElement = $form->getElement('super');
        $options = Model_NodeMapper::getSuperCandidates(Model_NodeMapper::getById($type->rootId), $type->id);
        $superElement->addMultiOptions($options);
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $array = explode('(', $type->name);
            $inverse = (isset($array[1])) ? trim(str_replace(['(', ')'], '', $array[1])) : '';
            $form->populate([
                'description' => $type->description,
                'inverse' => $inverse,
                'name' => trim($array[0]),
                'super' => $type->superId,
            ]);
            $this->view->form = $form;
            $this->view->type = $type;
            return;
        }
        $type->name = str_replace(['(', ')'], '', $form->getValue('name'));
        $inverse = trim(str_replace(['(', ')'], '', $form->getValue('inverse')));
        if ($inverse) {
            $type->name .= ' (' . $inverse . ')';
        }
        $type->description = $this->_getParam('description');
        $type->update();
        $superLink = Model_LinkMapper::getLink($type, $type->propertyToSuper);
        $superLink->range = Model_EntityMapper::getById($form->getValue('super'));
        $superLink->update();
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/hierarchy/#tab' . $type->rootId);
    }

    public function updateHierarchyAction() {

    }

    public function viewAction() {
        $type = Model_NodeMapper::getById($this->_getParam('id'));
        $linksEntitites = Model_LinkMapper::getLinkedEntities($type, $type->propertyToEntity, true);
        if ($type->class->code == 'E53') {
            $linksEntitites = [];
            foreach (Model_LinkMapper::getLinkedEntities($type, $type->propertyToEntity, true) as $object) {
                $linkedEntity = Model_LinkMapper::getLinkedEntity($object, 'P53', true);
                if ($linkedEntity) { // needed to remove node subs
                    $linksEntitites[] = $linkedEntity;
                }
            }
        }
        $this->view->type = $type;
        $this->view->linksEntities = $linksEntitites;
        $this->view->linksProperties = Model_LinkPropertyMapper::getByEntity($type);
    }

}
