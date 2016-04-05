<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_CarrierController extends Zend_Controller_Action {

    public function insertAction() {
        $form = new Admin_Form_Carrier();
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->menuHighlight = 'reference';
            $this->view->label = Model_ClassMapper::getByCode('E84')->nameTranslated;
            $this->view->objects = Model_EntityMapper::getByCodes('PhysicalObject');
            $this->view->form = $form;
            $this->view->typeTreeData = Model_NodeMapper::getTreeData('type', 'information carrier');
            return;
        }
        $carrier = Model_EntityMapper::insert('E84', $form->getValue('name'), $form->getValue('description'));
        $type = Model_NodeMapper::getRootType('type', 'information carrier');
        if ($this->_getParam('typeId')) {
            $type = Model_EntityMapper::getById($this->_getParam('typeId'));
        }
        Model_LinkMapper::insert('P2', $carrier, $type);
        if ($form->getValue('objectId')) {
            $place = Model_LinkMapper::getLinkedEntity($form->getValue('objectId'), 'P53');
            Model_LinkMapper::insert('OA8', $carrier, $place);
        }
        Model_DateMapper::saveDates($carrier, $form);
        $this->_helper->message('info_insert');
        // @codeCoverageIgnoreStart
        if ($form->getElement('continue')->getValue()) {
            return $this->_helper->redirector->gotoUrl('/admin/carrier/insert');
        }
        return $this->_helper->redirector->gotoUrl('/admin/carrier/view/id/' . $carrier->id);
        // @codeCoverageIgnoreEnd
    }

    public function updateAction() {
        $form = new Admin_Form_Carrier();
        $carrier = Model_EntityMapper::getById($this->_getParam('id'));
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $type = Model_NodeMapper::getNodeByEntity('type', 'Information Carrier', $carrier);
            $this->view->typeTreeData = Model_NodeMapper::getTreeData('type', 'information carrier', $type);
            $form->populate([
                'name' => $carrier->name,
                'description' => $carrier->description,
                'typeId' => Model_NodeMapper::getNodeByEntity('type', 'Information Carrier', $carrier)->id,

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
            $this->view->menuHighlight = 'reference';
            $this->view->carrier = $carrier;
            $this->view->form = $form;
            $this->view->objects = Model_EntityMapper::getByCodes('PhysicalObject');
            return;
        }
        $carrier->name = $form->getValue('name');
        $carrier->description = $form->getValue('description');
        $carrier->update();
        foreach (Model_LinkMapper::getLinks($carrier, 'P2') as $link) {
            $link->delete();
        }
        $type = Model_NodeMapper::getRootType('type', 'information carrier');
        if ($this->_getParam('typeId')) {
            $type = Model_EntityMapper::getById($this->_getParam('typeId'));
        }
        Model_LinkMapper::insert('P2', $carrier, $type);
        if (Model_LinkMapper::getLink($carrier, 'OA8')) {
            Model_LinkMapper::getLink($carrier, 'OA8')->delete();
        }
        //var_dump($form->getValue('objectId'));Die;
        if ($form->getValue('objectId')) {
            $place = Model_LinkMapper::getLinkedEntity($form->getValue('objectId'), 'P53');
            Model_LinkMapper::insert('OA8', $carrier, $place);
        }
        Model_DateMapper::saveDates($carrier, $form);
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/carrier/view/id/' . $carrier->id);
    }

    public function viewAction() {
        $carrier = Model_EntityMapper::getById($this->_getParam('id'));
        $sourceLinks = Model_LinkMapper::getLinks($carrier, 'P128');
        $this->view->dates = Model_DateMapper::getDates($carrier);
        $this->view->menuHighlight = 'reference';
        $this->view->carrier = $carrier;
        $this->view->carrierType = Model_NodeMapper::getNodeByEntity('type', 'Information Carrier', $carrier);
        $this->view->sourceLinks = $sourceLinks;
        $place = Model_LinkMapper::getLinkedEntity($carrier, 'OA8');
        if ($place) {
            $this->view->object = Model_LinkMapper::getLinkedEntity($place, 'P53', true);
        }
    }

    public function deleteAction() {
        $carrier = Model_EntityMapper::getById($this->_getParam('id'));
        $carrier->delete();
        $this->_helper->message('info_delete');
        return $this->_helper->redirector->gotoUrl('/admin/reference');
    }

}
