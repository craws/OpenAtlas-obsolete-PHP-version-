<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_ActorController extends Zend_Controller_Action {

    public function deleteAction() {
        Model_EntityMapper::getById($this->_getParam('id'))->delete();
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
            Model_LinkMapper::insert('P67', $source, $actor);
        }
        self::save($actor, $form, $hierarchies);
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
        foreach (Model_LinkMapper::getLinkedEntities($actor, 'P131') as $alias) {
            $alias->delete();
        }
        foreach (Model_LinkMapper::getLinks($actor, ['P2', 'P74', 'OA8', 'OA9']) as $link) {
            $link->delete();
        }
        self::save($actor, $form, $hierarchies);
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/actor/view/id/' . $actor->id);
    }

    public function viewAction() {
        $actor = Model_EntityMapper::getById($this->_getParam('id'));
        $this->view->actor = $actor;
        $this->view->aliases = Model_LinkMapper::getLinkedEntities($actor, 'P131');
        $this->view->dates = Model_DateMapper::getDates($actor);
        $this->view->relationInverseLinks = Model_LinkMapper::getLinks($actor, 'OA7', true);
        $this->view->relationLinks = Model_LinkMapper::getLinks($actor, 'OA7');
        $sourceLinks = [];
        $referenceLinks = [];
        foreach (Model_LinkMapper::getLinks($actor, 'P67', true) as $link) {
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
        $eventLinks = Model_LinkMapper::getLinks($actor, ['P11', 'P14', 'P22', 'P23'], true);
        $this->view->eventLinks = $eventLinks;
        $this->view->memberOfLinks = Model_LinkMapper::getLinks($actor, 'P107', true);
        if ($actor->class->code != 'E21') {
            $this->view->memberLinks = Model_LinkMapper::getLinks($actor, 'P107');
        }
        $objects = [];
        $residence = Model_LinkMapper::getLinkedEntity($actor, 'P74');
        if ($residence) {
            $object = Model_LinkMapper::getLinkedEntity($residence, 'P53', true);
            $objects[] = $object;
            $this->view->residence = $object;
        }
        $firstPlace = Model_LinkMapper::getLinkedEntity($actor, 'OA8');
        if ($firstPlace) {
            $object = Model_LinkMapper::getLinkedEntity($firstPlace, 'P53', true);
            $objects[] = $object;
            $this->view->first = $object;
        }
        $lastPlace = Model_LinkMapper::getLinkedEntity($actor, 'OA9');
        if ($lastPlace) {
            $object = Model_LinkMapper::getLinkedEntity($lastPlace, 'P53', true);
            $objects[] = $object;
            $this->view->last = $object;
        }
        foreach ($eventLinks as $link) {
            $event = $link->domain;
            $place = Model_LinkMapper::getLinkedEntity($event, 'P7');
            if ($place) {
                $objects[] = Model_LinkMapper::getLinkedEntity($place, 'P53', true);
            }
            $acquisition = Model_LinkMapper::getLinkedEntity($event, 'P24');
            if ($acquisition) {
                $objects[] = $acquisition;
            }
        }
        $this->view->objects = $objects;
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
            $place = Model_LinkMapper::getLinkedEntity($actor, $propertyCode);
            if ($place) {
                $object = Model_LinkMapper::getLinkedEntity($place, 'P53', true);
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
        $this->timelog .= sprintf('%04d', round((microtime(true) - $this->time) * 1000)) . ' insert type links<br/>';
        $this->time = microtime(true);
        Model_DateMapper::saveDates($entity, $form);
        $this->timelog .= sprintf('%04d', round((microtime(true) - $this->time) * 1000)) . ' save dates<br/>';
        $this->time = microtime(true);
        foreach (['residenceId' => 'P74', 'appearsFirstId' => 'OA8', 'appearsLastId' => 'OA9'] as $formField => $propertyCode) {
            if ($form->getValue($formField)) {
                $place = Model_LinkMapper::getLinkedEntity($form->getValue($formField), 'P53');
                Model_LinkMapper::insert($propertyCode, $entity, $place);
            }
        }
        $this->timelog .= sprintf('%04d', round((microtime(true) - $this->time) * 1000)) . ' save places<br/>';
        $this->time = microtime(true);
        $data = $form->getValues();
        foreach (array_unique($data['alias']) as $name) {
            if (trim($name)) {
                $aliasId = Model_EntityMapper::insert('E82', trim($name));
                Model_LinkMapper::insert('P131', $entity, $aliasId);
            }
        }
        $this->timelog .= sprintf('%04d', round((microtime(true) - $this->time) * 1000)) . ' save alias<br/>';
        $this->time = microtime(true);
    }

}
