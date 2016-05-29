<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_CarrierController extends Zend_Controller_Action {


    public function deleteAction() {
        Model_EntityMapper::getById($this->_getParam('id'))->delete();
        $this->_helper->message('info_delete');
        return $this->_helper->redirector->gotoUrl('/admin/reference');
    }

    public function insertAction() {
        $form = new Admin_Form_Carrier();
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->form = $form;
            $this->view->label = Model_ClassMapper::getByCode('E84')->nameTranslated;
            $this->view->menuHighlight = 'reference';
            $this->view->objects = Model_EntityMapper::getByCodes('PhysicalObject');
            $this->view->typeTreeData = Model_NodeMapper::getTreeData('information carrier');
            return;
        }
        $carrier = Model_EntityMapper::insert('E84', $form->getValue('name'), $form->getValue('description'));
        self::save($form, $carrier);
        $this->_helper->message('info_insert');
        // @codeCoverageIgnoreStart
        if ($form->getElement('continue')->getValue()) {
            return $this->_helper->redirector->gotoUrl('/admin/carrier/insert');
        }
        // @codeCoverageIgnoreEnd
        return $this->_helper->redirector->gotoUrl('/admin/carrier/view/id/' . $carrier->id);
    }

    public function updateAction() {
        $carrier = Model_EntityMapper::getById($this->_getParam('id'));
        $form = new Admin_Form_Carrier();
        $this->view->form = $form;
        $this->view->carrier = $carrier;
        $this->view->menuHighlight = 'reference';
        $type = Model_NodeMapper::getNodeByEntity('Information Carrier', $carrier);
        if (!$this->getRequest()->isPost()) {
            $form->populate([
                'name' => $carrier->name,
                'description' => $carrier->description,
                'typeId' => $type->id,
                'modified' => ($carrier->modified) ? $carrier->modified->getTimestamp() : 0
            ]);
            if ($type->rootId) {
                $form->populate(['typeButton' => $type->name]);
            }
            Admin_Form_Abstract::populateDates($form, $carrier, ['OA1' => 'begin', 'OA2' => 'end']);
            $place = Model_LinkMapper::getLinkedEntity($carrier, ['OA8']);
            if ($place) {
                $object = Model_LinkMapper::getLinkedEntity($place, 'P53', true);
                $form->populate([
                    'objectButton' => $object->name,
                    'objectId' => $object->id
                ]);
            }
            $this->view->typeTreeData = Model_NodeMapper::getTreeData('information carrier', $type);
            $this->view->objects = Model_EntityMapper::getByCodes('PhysicalObject');
            return;
        }
        $formValid = $form->isValid($this->getRequest()->getPost());
        $modified = Model_EntityMapper::checkIfModified($carrier, $form->modified->getValue());
        // @codeCoverageIgnoreStart
        if ($modified) {
            $log = Model_UserLogMapper::getLogForView('entity', $carrier->id);
            $this->view->modifier = $log['modifier_name'];
        }
        // @codeCoverageIgnoreEnd
        if (!$formValid || $modified) {
            $this->view->objects = Model_EntityMapper::getByCodes('PhysicalObject');
            $this->view->typeTreeData = Model_NodeMapper::getTreeData('information carrier');
            $this->_helper->message('error_modified');
            return;
        }
        $carrier->name = $form->getValue('name');
        $carrier->description = $form->getValue('description');
        $carrier->update();
        foreach (Model_LinkMapper::getLinks($carrier, ['P2', 'OA8']) as $link) {
            $link->delete();
        }
        self::save($form, $carrier);
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/carrier/view/id/' . $carrier->id);
    }

    public function viewAction() {
        $carrier = Model_EntityMapper::getById($this->_getParam('id'));
        $sourceLinks = Model_LinkMapper::getLinks($carrier, 'P128');
        $this->view->carrier = $carrier;
        $this->view->carrierType = Model_NodeMapper::getNodeByEntity('Information Carrier', $carrier);
        $this->view->dates = Model_DateMapper::getDates($carrier);
        $this->view->menuHighlight = 'reference';
        $this->view->sourceLinks = $sourceLinks;
        $place = Model_LinkMapper::getLinkedEntity($carrier, 'OA8');
        if ($place) {
            $this->view->object = Model_LinkMapper::getLinkedEntity($place, 'P53', true);
        }
    }

    private function save(Zend_Form $form, Model_Entity $carrier) {
        $type = Model_NodeMapper::getRootType('information carrier');
        if ($this->_getParam('typeId')) {
            $type = Model_EntityMapper::getById($this->_getParam('typeId'));
        }
        Model_LinkMapper::insert('P2', $carrier, $type);
        if ($form->getValue('objectId')) {
            $place = Model_LinkMapper::getLinkedEntity($form->getValue('objectId'), 'P53');
            Model_LinkMapper::insert('OA8', $carrier, $place);
        }
        Model_DateMapper::saveDates($carrier, $form);
    }

}
