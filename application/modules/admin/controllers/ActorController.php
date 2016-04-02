<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_ActorController extends Zend_Controller_Action {

    public function indexAction() {
        $this->view->actors = Model_EntityMapper::getByCodes('Actor');
    }

    public function addAction() {
        $origin = Model_EntityMapper::getById($this->_getParam('id'));
        $array = Zend_Registry::get('config')->get('codeView')->toArray();
        $controller = $array[$origin->getClass()->code];
        $this->view->menuHighlight = $controller;
        $this->view->controller = $controller;
        $this->view->origin = $origin;
        $this->view->actors = Model_EntityMapper::getByCodes('Actor');
    }

    public function linkAction() {
        $actor = Model_EntityMapper::getById($this->_getParam('actorId'));
        $entity = Model_EntityMapper::getById($this->_getParam('rangeId'));
        if (Model_LinkMapper::linkExists('P67', $entity, $actor)) {
            $this->_helper->message('error_link_exists');
        } else {
            Model_LinkMapper::insert('P67', $entity, $actor);
            $this->_helper->message('info_insert');
        }
        $array = Zend_Registry::get('config')->get('codeView')->toArray();
        $controller = $array[$entity->getClass()->code];
        return $this->_helper->redirector->gotoUrl('/admin/' . $controller . '/view/id/' . $entity->id . '/#tabActor');
    }

    public function viewAction() {
        $actor = Model_EntityMapper::getById($this->_getParam('id'));
        $this->view->actor = $actor;
        $this->view->relationLinks = Model_LinkMapper::getLinks($actor, 'OA7');
        $this->view->relationInverseLinks = Model_LinkMapper::getLinks($actor, 'OA7', true);
        $this->view->aliases = Model_LinkMapper::getLinkedEntities($actor, 'P131');
        $this->view->gender = Model_NodeMapper::getNodeByEntity('type', 'Gender', $actor);
        $sourceLinks = [];
        $referenceLinks = [];
        foreach (Model_LinkMapper::getLinks($actor, 'P67', true) as $link) {
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
        $this->view->dates = Model_DateMapper::getDates($actor);
        $eventLinks = Model_LinkMapper::getLinks($actor, ['P11', 'P14', 'P22', 'P23'], true);
        $this->view->eventLinks = $eventLinks;
        $this->view->memberOfLinks = Model_LinkMapper::getLinks($actor, 'P107', true);
        if ($actor->getClass()->code != 'E21') {
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
            $event = $link->getDomain();
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

    public function insertAction() {
        $class = Model_ClassMapper::getByCode($this->_getParam('code'));
        if (!$class) {
            $this->getHelper('viewRenderer')->setNoRender(true);
            $this->_helper->message('error_missing_class');
            return;
        }
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
        if ($class->code != 'E21') {
            $form->removeElement('birth');
            $form->removeElement('death');
            $form->removeElement('genderId');
            $form->removeElement('genderButton');
        }
        $form->addElement($form->createElement('text', 'alias0', ['belongsTo' => 'alias']));
        if ($this->getRequest()->isPost()) {
            Admin_Form_Abstract::preValidation($form, $this->getRequest()->getPost());
        }
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->objects = Model_EntityMapper::getByCodes('PhysicalObject');
            $this->view->form = $form;
            $this->view->source = $source;
            $this->view->event = $event;
            $this->view->className = $class->nameTranslated;
            if ($class->code == 'E21') {
                $this->view->genderTreeData = Model_NodeMapper::getTreeData('type', 'gender');
            }
            return;
        }
        $actor = Model_EntityMapper::insert($class->id, $form->getValue('name'), $form->getValue('description'));
        Model_DateMapper::saveDates($actor, $form);
        foreach (['residenceId' => 'P74', 'appearsFirstId' => 'OA8', 'appearsLastId' => 'OA9'] as $formField => $propertyCode) {
            if ($form->getValue($formField)) {
                $place = Model_LinkMapper::getLinkedEntity(Model_EntityMapper::getById($form->getValue($formField)), 'P53');
                Model_LinkMapper::insert($propertyCode, $actor, $place);
            }
        }
        if ($form->getValue('genderId')) {
            Model_LinkMapper::insert('P2', $actor, Model_EntityMapper::getById($form->getValue('genderId')));
        }
        $data = $form->getValues();
        foreach (array_unique($data['alias']) as $name) {
            if (trim($name)) {
                $alias = Model_EntityMapper::insert('E82', trim($name));
                Model_LinkMapper::insert('P131', $actor, $alias);
            }
        }
        if ($source) {
            Model_LinkMapper::insert('P67', $source, $actor);
        }
        $this->_helper->message('info_insert');
        // @codeCoverageIgnoreStart
        if ($event) {
            return $this->_helper->redirector->gotoUrl('/admin/involvement/insert/origin/event/eventId/' . $event->id . '/actorId/' . $actor->id);
        }
        if ($form->getElement('continue')->getValue() && $source) {
            return $this->_helper->redirector->gotoUrl('/admin/actor/insert/sourceId/' . $source->id . '/code/' . $class->code);
        }
        if ($form->getElement('continue')->getValue()) {
            return $this->_helper->redirector->gotoUrl('/admin/actor/insert/code/' . $class->code);
        }
        if ($source) {
            return $this->_helper->redirector->gotoUrl('/admin/source/view/id/' . $source->id . '/#tabActor');
        }
        return $this->_helper->redirector->gotoUrl('/admin/actor/view/id/' . $actor->id);
        // @codeCoverageIgnoreEnd
    }

    public function updateAction() {
        $actor = Model_EntityMapper::getById($this->_getParam('id'));
        $form = new Admin_Form_Actor();
        $aliasIndex = 0;
        $aliasElements = Model_LinkMapper::getLinkedEntities($actor, 'P131');
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
        $form->populate(['aliasElementId' => $aliasIndex]);
        if ($this->getRequest()->isPost()) {
            Admin_Form_Abstract::preValidation($form, $this->getRequest()->getPost());
        }
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $form->populate(['name' => $actor->name, 'description' => $actor->description]);
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
            Admin_Form_Abstract::populateDates($form, $actor, ['OA1' => 'begin', 'OA3' => 'begin', 'OA2' => 'end', 'OA4' => 'end']);
            if ($actor->getClass()->code != 'E21') {
                $form->removeElement('birth');
                $form->removeElement('death');
                $form->removeElement('genderId');
                $form->removeElement('genderButton');
            } else {
                $gender = Model_NodeMapper::getNodeByEntity('type', 'Gender', $actor);
                if ($gender) {
                    $form->populate(['genderId' => $gender->id, 'genderButton' => $gender->name]);
                }
                $this->view->genderTreeData = Model_NodeMapper::getTreeData('type', 'gender', $gender);
            }
            $this->view->objects = Model_EntityMapper::getByCodes('PhysicalObject');
            $this->view->actor = $actor;
            $this->view->form = $form;
            return;
        }
        $actor->name = $form->getValue('name');
        $actor->description = $form->getValue('description');
        $actor->update();
        Model_DateMapper::saveDates($actor, $form);
        foreach (['residenceId' => 'P74', 'appearsFirstId' => 'OA8', 'appearsLastId' => 'OA9'] as $formField => $propertyCode) {
            if (Model_LinkMapper::getLink($actor, $propertyCode)) {
                Model_LinkMapper::getLink($actor, $propertyCode)->delete();
            }
            if ($form->getValue($formField)) {
                $place = Model_LinkMapper::getLinkedEntity(Model_EntityMapper::getById($form->getValue($formField)), 'P53');
                Model_LinkMapper::insert($propertyCode, $actor, $place);
            }
        }
        foreach (Model_LinkMapper::getLinkedEntities($actor, 'P131') as $alias) {
            $alias->delete();
        }
        foreach (Model_LinkMapper::getLinks($actor, 'P2') as $link) {
            $link->delete();
        }
        if ($form->getValue('genderId')) {
            Model_LinkMapper::insert('P2', $actor, Model_EntityMapper::getById($form->getValue('genderId')));
        }
        $data = $form->getValues();
        foreach (array_unique($data['alias']) as $name) {
            if (trim($name)) {
                $alias = Model_EntityMapper::insert('E82', trim($name));
                Model_LinkMapper::insert('P131', $actor, $alias);
            }
        }
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/actor/view/id/' . $actor->id);
    }

    public function deleteAction() {
        Model_EntityMapper::getById($this->_getParam('id'))->delete();
        $this->_helper->message('info_delete');
        return $this->_helper->redirector->gotoUrl('/admin/actor');
    }

}
