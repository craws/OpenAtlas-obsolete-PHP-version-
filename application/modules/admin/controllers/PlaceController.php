<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_PlaceController extends Zend_Controller_Action {

    public function addAction() {
        $origin = Model_EntityMapper::getById($this->_getParam('id'));
        $array = Zend_Registry::get('config')->get('codeView')->toArray();
        $controller = $array[$origin->getClass()->code];
        $this->view->controller = $controller;
        $this->view->menuHighlight = $controller;
        $this->view->origin = $origin;
        $this->view->objects = Model_EntityMapper::getByCodes('PhysicalObject');
    }

    public function deleteAction() {
        Model_EntityMapper::getById($this->_getParam('id'))->delete();
        $this->_helper->message('info_delete');
        return $this->_helper->redirector->gotoUrl('/admin/place');
    }

    public function indexAction() {
        $this->view->objects = Model_EntityMapper::getByCodes('PhysicalObject');
        $this->view->jsonData = Model_GisMapper::getJsonData($this->view->objects);
    }

    public function insertAction() {
        $source = null;
        if ($this->_getParam('sourceId')) {
            $source = Model_EntityMapper::getById($this->_getParam('sourceId'));
            $this->view->menuHighlight = 'source';
        }
        $form = new Admin_Form_Place();
        $form->addElement($form->createElement('text', 'alias0', ['belongsTo' => 'alias']));
        if ($this->getRequest()->isPost()) {
            Admin_Form_Abstract::preValidation($form, $this->getRequest()->getPost());
        }
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->form = $form;
            $this->view->source = $source;
            $this->view->siteTreeData = Model_NodeMapper::getTreeData('type', 'site');
            $this->view->administrativeTreeData = Model_NodeMapper::getTreeData('place', 'administrative unit');
            $this->view->historicalTreeData = Model_NodeMapper::getTreeData('place', 'historical place');
            return;
        }
        $object = Model_EntityMapper::insert('E18', $form->getValue('name'), $form->getValue('description'));
        Model_LinkMapper::insert('P2', $object, Model_EntityMapper::getById($form->getValue('siteId')));
        $place = Model_EntityMapper::insert('E53', 'Location of ' . $form->getValue('name'));
        Model_LinkMapper::insert('P53', $object, $place);
        self::save($form, $object, $place);
        if ($source) {
            Model_LinkMapper::insert('P67', $source, $object);
        }
        $this->_helper->message('info_insert');
        // @codeCoverageIgnoreStart
        if ($form->getElement('continue')->getValue() && $source) {
            return $this->_helper->redirector->gotoUrl('/admin/place/insert/sourceId/' . $source->id);
        }
        if ($form->getElement('continue')->getValue()) {
            return $this->_helper->redirector->gotoUrl('/admin/place/insert');
        }
        if ($source) {
            return $this->_helper->redirector->gotoUrl('/admin/source/view/id/' . $source->id . '/#tabPlace');
        }
        // @codeCoverageIgnoreEnd
        return $this->_helper->redirector->gotoUrl('/admin/place/view/id/' . $object->id);
    }

    public function linkAction() {
        $place = Model_EntityMapper::getById($this->_getParam('placeId'));
        $entity = Model_EntityMapper::getById($this->_getParam('rangeId'));
        if (Model_LinkMapper::linkExists('P67', $entity, $place)) {
            $this->_helper->message('error_link_exists');
        } else {
            Model_LinkMapper::insert('P67', $entity, $place);
            $this->_helper->message('info_insert');
        }
        $array = Zend_Registry::get('config')->get('codeView')->toArray();
        $controller = $array[$entity->getClass()->code];
        return $this->_helper->redirector->gotoUrl('/admin/' . $controller . '/view/id/' . $entity->id . '/#tabPlace');
    }

    public function updateAction() {
        $object = Model_EntityMapper::getById($this->_getParam('id'));
        $place = Model_LinkMapper::getLinkedEntity($object, 'P53');
        $form = new Admin_Form_Place();
        $this->view->form = $form;
        $this->view->object = $object;
        $aliasIndex = 0;
        $aliasElements = Model_LinkMapper::getLinkedEntities($object, 'P1');
        if ($aliasElements) {
            foreach ($aliasElements as $alias) {
                $element = $form->createElement('text', 'alias' . $aliasIndex, ['belongsTo' => 'alias']);
                $element->setValue($alias->name);
                $form->addElement($element);
                $aliasIndex++;
            }
        } else {
            $element = $form->createElement('text', 'alias0', ['belongsTo' => 'alias']);
            $form->addElement($element);
            $aliasIndex++;
        }
        $form->populate(['aliasId' => $aliasIndex]);
        if (!$this->getRequest()->isPost()) {
            self::prepareDefaultUpdate($form, $object, $place);
            return;
        }
        Admin_Form_Abstract::preValidation($form, $this->getRequest()->getPost());
        $formValid = $form->isValid($this->getRequest()->getPost());
        $modified = Model_EntityMapper::checkIfModified($object, $form->modified->getValue());
        if ($modified) {
            $log = Model_UserLogMapper::getLogForView('entity', $object->id);
            $this->view->modifier = $log['modifier_name'];
        }
        if (!$formValid || $modified) {
            $this->view->siteTreeData = Model_NodeMapper::getTreeData('type', 'site');
            $this->view->administrativeTreeData = Model_NodeMapper::getTreeData('place', 'administrative unit');
            $this->view->historicalTreeData = Model_NodeMapper::getTreeData('place', 'historical place');
            $this->_helper->message('error_modified');
            return;
        }
        $object->name = $form->getValue('name');
        $object->description = $form->getValue('description');
        $object->update();
        foreach (Model_LinkMapper::getLinks($object, 'P2') as $objectLink) {
            $objectLink->delete();
        }
        Model_LinkMapper::insert('P2', $object, Model_EntityMapper::getById($form->getValue('siteId')));
        $place->name = 'Location of ' . $form->getValue('name');
        $place->update();
        Model_GisMapper::deleteByEntity($place);
        foreach (Model_LinkMapper::getLinkedEntities($object, 'P1') as $alias) {
            $alias->delete();
        }
        foreach (Model_LinkMapper::getLinks($place, 'P89') as $link) {
            $link->delete();
        }
        self::save($form, $object, $place);
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/place/view/id/' . $object->id);
    }

    public function viewAction() {
        $object = Model_EntityMapper::getById($this->_getParam('id'));
        $place = Model_LinkMapper::getLinkedEntity($object, 'P53');
        $this->view->gis = Model_GisMapper::getByEntity($place);
        // @codeCoverageIgnoreStart
        if ($this->view->gis) {
            $this->view->jsonData = Model_GisMapper::getJsonData();
        }
        // @codeCoverageIgnoreEnd
        $this->view->object = $object;
        $this->view->events = Model_LinkMapper::getLinkedEntities($place, 'P7', true);
        $this->view->aliases = Model_LinkMapper::getLinkedEntities($object, 'P1');
        $this->view->site = Model_NodeMapper::getNodeByEntity('type', 'Site', $object);
        $this->view->administrative = Model_NodeMapper::getNodesByEntity('place', 'Administrative Unit', $place);
        $this->view->historicals = Model_NodeMapper::getNodesByEntity('place', 'Historical Place', $place);
        $this->view->dates = Model_DateMapper::getDates($object);
        $this->view->events = array_merge(
            Model_LinkMapper::getLinkedEntities($place, 'P7', true), Model_LinkMapper::getLinkedEntities($object, 'P24', true)
        );
        $this->view->actorLinks = array_merge(
            Model_LinkMapper::getLinks($place, 'P74', true),
            Model_LinkMapper::getLinks($place, 'OA8', true),
            Model_LinkMapper::getLinks($place, 'OA9', true)
        );
        $sourceLinks = [];
        $referenceLinks = [];
        foreach (Model_LinkMapper::getLinks($object, 'P67', true) as $link) {
            switch ($link->getDomain()->getClass()->code) {
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
        $site = Model_NodeMapper::getNodeByEntity('type', 'Site', $object);
        $form->populate([
            'name' => $object->name,
            'description' => $object->description,
            'siteId' => $site->id,
            'siteButton' => $site->name,
            'modified' => ($object->modified) ? $object->modified->getTimestamp() : 0
        ]);
        $gis = Model_GisMapper::getByEntity($place);
        // @codeCoverageIgnoreStart
        if ($gis) {
            $form->populate(['easting' => $gis->easting, 'northing' => $gis->northing]);
        }
        // @codeCoverageIgnoreEnd
        Admin_Form_Abstract::populateDates($form, $object, ['OA1' => 'begin', 'OA2' => 'end']);
        $this->view->siteTreeData = Model_NodeMapper::getTreeData('type', 'site', $site);
        $administratives = Model_NodeMapper::getNodesByEntity('place', 'Administrative Unit', $place);
        $this->view->administratives = $administratives;
        $this->view->administrativeTreeData = Model_NodeMapper::getTreeData('place', 'administrative unit', $administratives);
        $historicals = Model_NodeMapper::getNodesByEntity('place', 'Historical Place', $place);
        $this->view->historicals = $historicals;
        $this->view->historicalTreeData = Model_NodeMapper::getTreeData('place', 'historical place', $historicals);
        $this->view->object = $object;
        return;
    }

    private function save(Zend_Form $form, Model_Entity $object, Model_Entity $place) {
        Model_DateMapper::saveDates($object, $form);
        if ($form->getValue('administrativeId')) {
            foreach (explode(",", $form->getValue('administrativeId')) as $id) {
                Model_LinkMapper::insert('P89', $place, Model_EntityMapper::getById($id));
            }
        }
        if ($form->getValue('historicalId')) {
            foreach (explode(",", $form->getValue('historicalId')) as $id) {
                Model_LinkMapper::insert('P89', $place, Model_EntityMapper::getById($id));
            }
        }
        $data = $form->getValues();
        foreach (array_unique($data['alias']) as $name) {
            if (trim($name)) {
                $alias = Model_EntityMapper::insert('E41', trim($name));
                Model_LinkMapper::insert('P1', $object, $alias);
            }
        }
        // @codeCoverageIgnoreStart
        if ($form->getValue('easting') && $form->getValue('northing')) {
            $gis = new Model_Gis();
            $gis->setEntity($place);
            $gis->easting = $form->getValue('easting');
            $gis->northing = $form->getValue('northing');
            $gis->insert();
        }
        // @codeCoverageIgnoreEnd

    }

}
