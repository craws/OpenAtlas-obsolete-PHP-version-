<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_PlaceController extends Zend_Controller_Action {

    public function deleteAction() {
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        Model_EntityMapper::getById($this->_getParam('id'))->delete();
        Model_UserLogMapper::insert('entity', $this->_getParam('id'), 'delete');
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_delete');
        return $this->_helper->redirector->gotoUrl('/admin/place');
    }

    public function indexAction() {
        $this->view->objects = Model_EntityMapper::getByCodes('PhysicalObject');
        $this->view->gisData = Model_GisMapper::getAll();
    }

    public function insertAction() {
        $source = null;
        if ($this->_getParam('sourceId')) {
            $source = Model_EntityMapper::getById($this->_getParam('sourceId'));
            $this->view->menuHighlight = 'source';
        }
        $form = new Admin_Form_Place();
        $hierarchies = $form->addHierarchies('Place');
        $form->addElement($form->createElement('text', 'alias0', ['belongsTo' => 'alias']));
        if ($this->getRequest()->isPost()) {
            $form->preValidation($this->getRequest()->getPost());
        }
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->form = $form;
            $this->view->source = $source;
            $this->view->gisData = Model_GisMapper::getAll();
            return;
        }
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        $objectId = Model_EntityMapper::insert('E18', $form->getValue('name'), $form->getValue('description'));
        $object = Model_EntityMapper::getById($objectId);
        $placeId = Model_EntityMapper::insert('E53', 'Location of ' . $form->getValue('name'));
        $place = Model_EntityMapper::getById($placeId);
        Model_LinkMapper::insert('P53', $objectId, $placeId);
        self::save($form, $object, $place, $hierarchies);
        if ($source) {
            $source->link('P67', $object);
        }
        Model_UserLogMapper::insert('entity', $objectId, 'insert');
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_insert');
        $url = '/admin/place/view/id/' . $object->id;
        if ($form->getElement('continue')->getValue() && $source) {
            $url = '/admin/place/insert/sourceId/' . $source->id;
        } else if ($form->getElement('continue')->getValue()) {
            $url = '/admin/place/insert';
        } else if ($source) {
            $url = '/admin/source/view/id/' . $source->id . '/#tabPlace';
        }
        return $this->_helper->redirector->gotoUrl($url);
    }

    public function updateAction() {
        $object = Model_EntityMapper::getById($this->_getParam('id'));
        $place = $object->getLinkedEntity('P53');
        $form = new Admin_Form_Place();
        $hierarchies = $form->addHierarchies('Place', $object);
        $this->view->form = $form;
        $this->view->object = $object;
        $form->prepareUpdate($object);
        if (!$this->getRequest()->isPost()) {
            self::prepareDefaultUpdate($form, $object, $place);
            return;
        }
        $form->preValidation($this->getRequest()->getPost());
        $formValid = $form->isValid($this->getRequest()->getPost());
        $modified = Model_EntityMapper::checkIfModified($object, $form->modified->getValue());
        if ($modified) {
            $log = Model_UserLogMapper::getLogForView('entity', $object->id);
            $this->view->modifier = $log['modifier_name'];
        }
        if (!$formValid || $modified) {
            $this->_helper->message('error_modified');
            return;
        }
        $object->name = $form->getValue('name');
        $object->description = $form->getValue('description');
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        $object->update();
        foreach ($object->getLinks('P2') as $objectLink) {
            $objectLink->delete();
        }
        $place->name = 'Location of ' . $form->getValue('name');
        $place->update();
        Model_GisMapper::deleteByEntity($place);
        foreach ($object->getLinkedEntities('P1') as $alias) {
            $alias->delete();
        }
        foreach ($place->getLinks('P89') as $link) {
            $link->delete();
        }
        self::save($form, $object, $place, $hierarchies);
        Model_UserLogMapper::insert('entity', $object->id, 'update');
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/place/view/id/' . $object->id);
    }

    public function viewAction() {
        $object = Model_EntityMapper::getById($this->_getParam('id'));
        $place = $object->getLinkedEntity('P53');
        $this->view->gisData = Model_GisMapper::getAll($object->id);
        $this->view->object = $object;
        $this->view->aliases = $object->getLinkedEntities('P1');
        $this->view->dates = Model_DateMapper::getDates($object);
        $this->view->administrative = Model_NodeMapper::getNodesByEntity('Administrative Unit', $object);
        $this->view->historicals = Model_NodeMapper::getNodesByEntity('Historical Place', $object);
        $this->view->events = array_merge(
            $place->getLinkedEntities('P7', true), $object->getLinkedEntities('P24', true)
        );
        $this->view->actorLinks = $place->getLinks(['P74', 'OA8', 'OA9'], true);
        $sourceLinks = [];
        $referenceLinks = [];
        foreach ($object->getLinks('P67', true) as $link) {
            switch ($link->domain->class->code) {
                case 'E31':
                    $referenceLinks[] = $link;
                    break;
                case 'E33':
                    $sourceLinks[] = $link;
                    break;
            }
        }
        $this->view->sourceLinks = $sourceLinks;
        $this->view->referenceLinks = $referenceLinks;
    }

    private function prepareDefaultUpdate(Zend_Form $form, Model_Entity $object, Model_Entity $place) {
        $gisData = Model_GisMapper::getAll($object->id);
        $this->view->gisData = $gisData;
        $form->populate([
            'name' => $object->name,
            'description' => $object->description,
            'modified' => ($object->modified) ? $object->modified->getTimestamp() : 0
        ]);
        $form->populateDates($object, ['OA1' => 'begin', 'OA2' => 'end']);
        return;
    }

    private function save(Zend_Form $form, Model_Entity $object, Model_Entity $place, array $hierarchies) {
        foreach ($hierarchies as $hierarchy) {
            $idField = $hierarchy->nameClean . 'Id';
            if ($form->getValue($idField)) {
                if ($hierarchy->propertyToEntity == 'P89') {
                    foreach (explode(",", $form->getValue($idField)) as $id) {
                        $place->link($hierarchy->propertyToEntity, $id);
                    }
                } else {
                    foreach (explode(",", $form->getValue($idField)) as $id) {
                        $object->link($hierarchy->propertyToEntity, $id);
                    }
                }
            } else if ($hierarchy->system && $hierarchy->propertyToEntity != 'P89') {
                $object->link($hierarchy->propertyToEntity, $hierarchy);
            }
        }
        Model_DateMapper::saveDates($object, $form);
        $data = $form->getValues();
        foreach (array_unique($data['alias']) as $name) {
            if (trim($name)) {
                $aliasId = Model_EntityMapper::insert('E41', trim($name));
                $object->link('P1', $aliasId);
            }
        }
        Model_GisMapper::insert($place, $form);
    }

}
