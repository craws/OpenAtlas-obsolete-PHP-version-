<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_EventController extends Zend_Controller_Action {

    public function init() {
        $this->rootEvent = Zend_Registry::get('rootEvent');
        $this->view->rootEvent = $this->rootEvent;
    }

    public function deleteAction() {
        $event = Model_EntityMapper::getById($this->_getParam('id'));
        if ($event->id != $this->rootEvent->id) {
            Zend_Db_Table::getDefaultAdapter()->beginTransaction();
            $event->delete();
            Zend_Db_Table::getDefaultAdapter()->commit();
            $this->_helper->message('info_delete');
        }
        return $this->_helper->redirector->gotoUrl('/admin/event');
    }

    public function indexAction() {
        $this->view->events = Model_EntityMapper::getByCodes('Event');
    }

    public function insertAction() {
        if (!in_array($this->_getParam('code'), Zend_Registry::get('config')->get('codeEvent')->toArray())) {
            $this->getHelper('viewRenderer')->setNoRender(true);
            $this->_helper->message('error_missing_class');
            return;
        }
        $class = Model_ClassMapper::getByCode($this->_getParam('code'));
        $source = null;
        if ($this->_getParam('sourceId')) {
            $source = Model_EntityMapper::getById($this->_getParam('sourceId'));
            $this->view->menuHighlight = 'source';
        }
        $actor = null;
        if ($this->_getParam('actorId')) {
            $actor = Model_EntityMapper::getById($this->_getParam('actorId'));
            $this->view->menuHighlight = 'actor';
        }
        $form = new Admin_Form_Event();
        $form->addFields($class);
        $hierarchies = $form->addHierarchies('Event');
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->form = $form;
            $this->view->source = $source;
            $this->view->actor = $actor;
            $this->view->class = $class;
            $this->view->actors = Model_EntityMapper::getByCodes('Actor');
            $this->view->objects = Model_EntityMapper::getByCodes('PhysicalObject');
            $this->view->events = Model_EntityMapper::getByCodes('Event');
            return;
        }
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        $eventId = Model_EntityMapper::insert($class->id, $form->getValue('name'), $form->getValue('description'));
        $event = Model_EntityMapper::getById($eventId);
        self::save($event, $form, $hierarchies);
        if ($source) {
            Model_LinkMapper::insert('P67', $source, $event);
        }
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_insert');
        $url = '/admin/event/view/id/' . $event->id;
        if ($actor) {
            $url = '/admin/involvement/insert/origin/actor/eventId/' . $event->id . '/actorId/' . $actor->id;
        } else if ($form->getElement('continue')->getValue() && $source) {
            $url = '/admin/event/insert/code/' . $class->code . '/sourceId/' . $source->id;
        } else if ($form->getElement('continue')->getValue()) {
            $url = '/admin/event/insert/code/' . $class->code;
        } else if ($source) {
            $url = '/admin/source/view/id/' . $source->id . '/#tabEvent';
        }
        return $this->_helper->redirector->gotoUrl($url);
    }

    public function updateAction() {
        $event = Model_EntityMapper::getById($this->_getParam('id'));
        $this->view->event = $event;
        // @codeCoverageIgnoreStart
        if ($event->id == $this->rootEvent->id) { // prevent update of root event
            return $this->_helper->redirector->gotoUrl('/admin/event/view/id/' . $event->id);
        }
        // @codeCoverageIgnoreEnd
        $form = new Admin_Form_Event();
        $form->addFields($event->class);
        $hierarchies = $form->addHierarchies('Event', $event);
        $this->view->form = $form;
        if (!$this->getRequest()->isPost()) {
            self::prepareDefaultUpdate($event, $form);
            return;
        }
        $formValid = $form->isValid($this->getRequest()->getPost());
        $modified = Model_EntityMapper::checkIfModified($event, $form->modified->getValue());
        if ($modified) {
            $log = Model_UserLogMapper::getLogForView('entity', $event->id);
            $this->view->modifier = $log['modifier_name'];
        }
        if (!$formValid || $modified) {
            $this->view->class = $event->class;
            $this->view->actors = Model_EntityMapper::getByCodes('Actor');
            $this->view->objects = Model_EntityMapper::getByCodes('PhysicalObject');
            $this->view->events = Model_EntityMapper::getByCodes('Event');
            $this->_helper->message('error_modified');
            return;
        }
        $event->name = $form->getValue('name');
        $event->description = $form->getValue('description');
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        $event->update();
        foreach (Model_LinkMapper::getLinks($event, ['P2', 'P7', 'P117', 'P22', 'P23', 'P24']) as $link) {
            $link->delete();
        }
        self::save($event, $form, $hierarchies);
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/event/view/id/' . $event->id);
    }

    public function viewAction() {
        $event = Model_EntityMapper::getById($this->_getParam('id'));
        $this->view->actorLinks = Model_LinkMapper::getLinks($event, ['P11', 'P14', 'P22', 'P23']);
        $this->view->event = $event;
        $this->view->dates = Model_DateMapper::getDates($event);
        $this->view->subs = Model_LinkMapper::getLinkedEntities($event, 'P117', true);
        $this->view->super = Model_LinkMapper::getLinkedEntity($event, 'P117');
        if ($event->class->name == 'Acquisition') {
            $this->view->acquisitionRecipient = Model_LinkMapper::getLinkedEntity($event, 'P22');
            $this->view->acquisitionDonor = Model_LinkMapper::getLinkedEntity($event, 'P23');
            $this->view->acquisitionPlace = Model_LinkMapper::getLinkedEntity($event, 'P24');
        }
        $sourceLinks = [];
        $referenceLinks = [];
        foreach (Model_LinkMapper::getLinks($event, 'P67', true) as $link) {
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
        $place = Model_LinkMapper::getLinkedEntity($event, 'P7');
        if ($place) {
            $this->view->place = Model_LinkMapper::getLinkedEntity($place, 'P53', true);
        }
    }

    private function prepareDefaultUpdate(Model_Entity $event, Zend_Form $form) {
        $form->populate([
            'name' => $event->name,
            'description' => $event->description,
            'modified' => ($event->modified) ? $event->modified->getTimestamp() : 0,
        ]);
        $superEvent = Model_LinkMapper::getLinkedEntity($event, 'P117');
        if ($superEvent->id != $this->rootEvent->id) {
            $form->populate(['superId' => $superEvent->id]);
            $form->populate(['superButton' => $superEvent->name]);
        }
        if ($event->class->name == 'Acquisition') {
            $recipient = Model_LinkMapper::getLinkedEntity($event, 'P22');
            if ($recipient) {
                $form->populate([
                    'recipientButton' => $recipient->name,
                    'recipientId' => $recipient->id
                ]);
            }
            $donor = Model_LinkMapper::getLinkedEntity($event, 'P23');
            if ($donor) {
                $form->populate([
                    'donorButton' => $donor->name,
                    'donorId' => $donor->id
                ]);
            }
            $acquisitionPlace = Model_LinkMapper::getLinkedEntity($event, 'P24');
            if ($acquisitionPlace) {
                $form->populate([
                    'acquisitionPlaceButton' => $acquisitionPlace->name,
                    'acquisitionPlaceId' => $acquisitionPlace->id
                ]);
            }
        }
        $location = Model_LinkMapper::getLinkedEntity($event, 'P7');
        if ($location) {
            $place = Model_LinkMapper::getLinkedEntity($location, 'P53', true);
            $form->populate([
                'placeButton' => $place->name,
                'placeId' => $place->id
            ]);
        }
        $form->populateDates($event, ['OA5' => 'begin', 'OA6' => 'end']);
        $this->view->class = $event->class;
        $this->view->actors = Model_EntityMapper::getByCodes('Actor');
        $this->view->objects = Model_EntityMapper::getByCodes('PhysicalObject');
        $this->view->events = Model_EntityMapper::getByCodes('Event');
    }

    private function save(Model_Entity $event, Zend_Form $form, array $hierarchies) {
        Model_LinkMapper::insertTypeLinks($event, $form, $hierarchies);
        Model_DateMapper::saveDates($event, $form);
        if ($form->getValue('placeId')) {
            $place = Model_LinkMapper::getLinkedEntity($form->getValue('placeId'), 'P53');
            Model_LinkMapper::insert('P7', $event, $place);
        }
        $superEventId = ($form->getValue('superId')) ? $form->getValue('superId') : $this->rootEvent->id;
        Model_LinkMapper::insert('P117', $event, $superEventId);
        if ($event->class->name == 'Acquisition') {
            if ($this->_getParam('recipientId')) {
                Model_LinkMapper::insert('P22', $event, $this->_getParam('recipientId'));
            }
            if ($this->_getParam('donorId')) {
                Model_LinkMapper::insert('P23', $event, $this->_getParam('donorId'));
            }
            if ($this->_getParam('acquisitionPlaceId')) {
                Model_LinkMapper::insert('P24', $event, $this->_getParam('acquisitionPlaceId'));
            }
        }
    }
}
