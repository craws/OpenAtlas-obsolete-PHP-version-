<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_ActorController extends Zend_Controller_Action {

    public function deleteAction() {
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        Model_EntityMapper::getById($this->_getParam('id'))->delete();
        Model_UserLogMapper::insert('entity', $this->_getParam('id'), 'delete');
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_delete');
        return $this->_helper->redirector->gotoUrl('/admin/actor');
    }

    public function indexAction() {
        $this->view->actors = Model_EntityMapper::getByCodes('Actor');
    }

    public function insertAction() {
        if (!in_array($this->_getParam('code'), Zend_Registry::get('config')->get('codeActor')->toArray())) {
            $this->getHelper('viewRenderer')->setNoRender(true);
            $this->_helper->message('error_missing_class');
            return;
        }
        $class = Model_ClassMapper::getByCode($this->_getParam('code'));
        $source = null;
        $event = null;
        if ($this->_getParam('sourceId')) {
            $source = Model_EntityMapper::getById($this->_getParam('sourceId'));
            $this->view->menuHighlight = 'source';
        } else if ($this->_getParam('eventId')) {
            $event = Model_EntityMapper::getById($this->_getParam('eventId'));
            $this->view->menuHighlight = 'event';
        }
        $form = new Admin_Form_Actor();
        $hierarchies = $form->addHierarchies($this->getFormName($this->_getParam('code')));
        if ($class->code != 'E21') {
            $form->removeElement('birth');
            $form->removeElement('death');
        }
        $form->addElement($form->createElement('text', 'alias0', ['belongsTo' => 'alias']));
        if ($this->getRequest()->isPost()) {
            $form->preValidation($this->getRequest()->getPost());
        }
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->className = $class->nameTranslated;
            $this->view->event = $event;
            $this->view->form = $form;
            $this->view->hierarchies = $hierarchies;
            $this->view->objects = Model_EntityMapper::getByCodes('PhysicalObject');
            $this->view->source = $source;
            return;
        }
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        $actorId = Model_EntityMapper::insert($class->id, $form->getValue('name'), $form->getValue('description'));
        $actor = Model_EntityMapper::getById($actorId);
        if ($source) {
            $source->link('P67', $actor);
        }
        self::save($actor, $form, $hierarchies);
        Model_UserLogMapper::insert('entity', $actorId, 'insert');
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_insert');
        $url = '/admin/actor/view/id/' . $actor->id;
        if ($event) {
            $url = '/admin/involvement/insert/origin/event/eventId/' . $event->id . '/actorId/' . $actor->id;
        } else if ($form->getElement('continue')->getValue() && $source) {
            $url = '/admin/actor/insert/sourceId/' . $source->id . '/code/' . $class->code;
        } else if ($form->getElement('continue')->getValue()) {
            $url = '/admin/actor/insert/code/' . $class->code;
        } else if ($source) {
            $url = '/admin/source/view/id/' . $source->id . '/#tabActor';
        }
        return $this->_helper->redirector->gotoUrl($url);
    }

    public function updateAction() {
        $actor = Model_EntityMapper::getById($this->_getParam('id'));
        $this->view->actor = $actor;
        $form = new Admin_Form_Actor();
        $form->prepareUpdate($actor);
        $this->view->form = $form;
        $hierarchies = $form->addHierarchies($this->getFormName($actor->class->code), $actor);
        if (!$this->getRequest()->isPost()) {
            self::prepareDefaultUpdate($form, $actor);
            return;
        }
        $form->preValidation($this->getRequest()->getPost());
        $formValid = $form->isValid($this->getRequest()->getPost());
        $modified = Model_EntityMapper::checkIfModified($actor, $form->modified->getValue());
        // @codeCoverageIgnoreStart
        if ($modified) {
            $log = Model_UserLogMapper::getLogForView('entity', $actor->id);
            $this->view->modifier = $log['modifier_name'];
        }
        // @codeCoverageIgnoreEnd
        if (!$formValid || $modified) {
            $this->view->objects = Model_EntityMapper::getByCodes('PhysicalObject');
            $this->_helper->message('error_modified');
            return;
        }
        $actor->name = $form->getValue('name');
        $actor->description = $form->getValue('description');
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        $actor->update();
        foreach ($actor->getLinkedEntities('P131') as $alias) {
            $alias->delete();
        }
        foreach ($actor->getLinks(['P2', 'P74', 'OA8', 'OA9']) as $link) {
            $link->delete();
        }
        self::save($actor, $form, $hierarchies);
        Model_UserLogMapper::insert('entity', $actor->id, 'update');
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/actor/view/id/' . $actor->id);
    }

    public function viewAction() {
        $actor = Model_EntityMapper::getById($this->_getParam('id'));
        $this->view->actor = $actor;
        $this->view->aliases = $actor->getLinkedEntities('P131');
        $this->view->dates = Model_DateMapper::getDates($actor);
        $this->view->relationInverseLinks = $actor->getLinks('OA7', true);
        $this->view->relationLinks = $actor->getLinks('OA7');
        $sourceLinks = [];
        $referenceLinks = [];
        foreach ($actor->getLinks('P67', true) as $link) {
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
        $eventLinks = $actor->getLinks(['P11', 'P14', 'P22', 'P23'], true);
        $this->view->eventLinks = $eventLinks;
        $this->view->memberOfLinks = $actor->getLinks('P107', true);
        if ($actor->class->code != 'E21') {
            $this->view->memberLinks = $actor->getLinks('P107');
        }
        $objectIds = [];
        $residence = $actor->getLinkedEntity('P74');
        if ($residence) {
            $object = $residence->getLinkedEntity('P53', true);
            $objectIds[] = $object->id;
            $this->view->residence = $object;
        }
        $firstPlace = $actor->getLinkedEntity('OA8');
        if ($firstPlace) {
            $object = $firstPlace->getLinkedEntity('P53', true);
            $objectIds[] = $object->id;
            $this->view->first = $object;
        }
        $lastPlace = $actor->getLinkedEntity('OA9');
        if ($lastPlace) {
            $object = $lastPlace->getLinkedEntity('P53', true);
            $objectIds[] = $object->id;
            $this->view->last = $object;
        }
        foreach ($eventLinks as $link) {
            $event = $link->domain;
            $place = $event->getLinkedEntity('P7');
            if ($place) {
                $object = $place->getLinkedEntity('P53', true);
                $objectIds[] = $object->id;
            }
            $acquisition = $event->getLinkedEntity('P24');
            if ($acquisition) {
                $objectIds[] = $acquisition->id;
            }
        }
        if ($objectIds) {
            $this->view->gisData = Model_GisMapper::getAll($objectIds);
        }
    }

    private function getFormName($code) {
        switch ($code) {
            case 'E21':
                $formName = 'Person';
                break;
            case 'E74':
                $formName = 'Group';
                break;
            case 'E40':
                $formName = 'Legal Body';
                break;
        }
        return $formName;
    }

    private function prepareDefaultUpdate(Zend_Form $form, Model_Entity $actor) {
        $form->populate([
            'name' => $actor->name,
            'description' => $actor->description,
            'modified' => ($actor->modified) ? $actor->modified->getTimestamp() : 0
        ]);
        foreach (['residence' => 'P74', 'appearsFirst' => 'OA8', 'appearsLast' => 'OA9'] as $formField => $propertyCode) {
            $place = $actor->getLinkedEntity($propertyCode);
            if ($place) {
                $object = $place->getLinkedEntity('P53', true);
                $form->populate([
                    $formField . 'Id' => $object->id,
                    $formField . 'Button' => $object->name
                ]);
            }
        }
        $form->populateDates($actor, ['OA1' => 'begin', 'OA3' => 'begin', 'OA2' => 'end', 'OA4' => 'end']);
        $this->view->objects = Model_EntityMapper::getByCodes('PhysicalObject');
    }

    private function save(Model_Entity $entity, Zend_Form $form, array $hierarchies) {
        Model_LinkMapper::insertTypeLinks($entity, $form, $hierarchies);
        Model_DateMapper::saveDates($entity, $form);
        foreach (['residenceId' => 'P74', 'appearsFirstId' => 'OA8', 'appearsLastId' => 'OA9'] as $formField => $propertyCode) {
            if ($form->getValue($formField)) {
                $place = Model_LinkMapper::getLinkedEntity($form->getValue($formField), 'P53');
                $entity->link($propertyCode, $place);
            }
        }
        $data = $form->getValues();
        foreach (array_unique($data['alias']) as $name) {
            if (trim($name)) {
                $aliasId = Model_EntityMapper::insert('E82', trim($name));
                $entity->link('P131', $aliasId);
            }
        }
    }

}
