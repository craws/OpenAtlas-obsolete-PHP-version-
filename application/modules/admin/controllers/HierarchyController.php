<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_HierarchyController extends Zend_Controller_Action {

    public function deleteAction() {
        $type = Model_NodeMapper::getById($this->_getParam('id'));
        if (!$type->extendable ||
            (!$type->superId && ($type->system && !in_array(Zend_Registry::get('user')->group, ['admin', 'manager'])))) {
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
        $this->view->nodes = $nodes;
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
        foreach (Zend_Registry::get('nodes') as $node) {
            if ($node->nameClean == \Craws\FilterInput::filter($form->getValue('name'), 'node')) {
                $this->view->form = $form;
                $this->_helper->message('error_name_exists');
                return;
            }
        }
        $hierarchy = Model_EntityMapper::insert('E55', $form->getValue('name'), $form->getValue('description'));
        Model_NodeMapper::insertHierarchy($form, $hierarchy);
        $this->_helper->message('info_insert');
        return $this->_helper->redirector->gotoUrl('/admin/hierarchy/#menuTabCustom');
    }

    public function updateAction() {
        $node = Model_NodeMapper::getById($this->_getParam('id'));
        if (!$node->superId || !$node->extendable) {
            $this->_helper->message('error_forbidden');
            return $this->_helper->redirector->gotoUrl('/admin/hierarchy');
        }
        $form = new Admin_Form_Node();
        if (!$node->directional) {
            $form->removeElement('inverse');
        }
        $superElement = $form->getElement('super');
        $options = Model_NodeMapper::getSuperCandidates(Model_NodeMapper::getById($node->rootId), $node->id);
        $superElement->addMultiOptions($options);
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $array = explode('(', $node->name);
            $inverse = (isset($array[1])) ? trim(str_replace(['(', ')'], '', $array[1])) : '';
            $form->populate([
                'description' => $node->description,
                'inverse' => $inverse,
                'name' => trim($array[0]),
                'super' => $node->superId,
            ]);
            $this->view->form = $form;
            $this->view->type = $node;
            return;
        }
        $node->name = str_replace(['(', ')'], '', $form->getValue('name'));
        $inverse = trim(str_replace(['(', ')'], '', $form->getValue('inverse')));
        if ($inverse) {
            $node->name .= ' (' . $inverse . ')';
        }
        $node->description = $this->_getParam('description');
        $node->update();
        $superLink = Model_LinkMapper::getLink($node, $node->propertyToSuper);
        $superLink->range = Model_EntityMapper::getById($form->getValue('super'));
        $superLink->update();
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/hierarchy/#tab' . $node->rootId);
    }

    public function updateHierarchyAction() {
        $hierarchy = Model_NodeMapper::getById($this->_getParam('id'));
        $form = new Admin_Form_Hierarchy();
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->hierarchy = $hierarchy;
            $this->view->form = $form;
            $form->populate([
                'description' => $hierarchy->description,
                'name' => $hierarchy->name,
                'multiple' => $hierarchy->multiple
            ]);
            foreach ($hierarchy->forms as $hierarchyForm) {
                $form->forms->removeMultiOption($hierarchyForm['id']);
            }
            return;
        }
        if ($hierarchy->name != $form->getValue('name')) {
            foreach (Zend_Registry::get('nodes') as $node) {
                if ($node->nameClean == \Craws\FilterInput::filter($form->getValue('name'), 'node')) {
                    $this->view->form = $form;
                    $this->_helper->message('error_name_exists');
                    return;
                }
            }
        }
        $hierarchy->name = $form->getValue('name');
        $hierarchy->description = $form->getValue('description');
        $hierarchy->update();
        Model_NodeMapper::updateHierarchy($form, $hierarchy);
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/hierarchy/#tab' . $hierarchy->id);
    }

    public function viewAction() {
        $node = Model_NodeMapper::getById($this->_getParam('id'));
        $linksEntitites = Model_LinkMapper::getLinkedEntities($node, $node->propertyToEntity, true);
        if ($node->class->code == 'E53') {
            $linksEntitites = [];
            foreach (Model_LinkMapper::getLinkedEntities($node, $node->propertyToEntity, true) as $object) {
                $linkedEntity = Model_LinkMapper::getLinkedEntity($object, 'P53', true);
                if ($linkedEntity) { // needed to remove node subs
                    $linksEntitites[] = $linkedEntity;
                }
            }
        }
        $this->view->node = $node;
        $this->view->linksEntities = $linksEntitites;
        $this->view->linksProperties = Model_LinkPropertyMapper::getByEntity($node);
    }

}
