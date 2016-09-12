<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_InvolvementController extends Zend_Controller_Action {

    public function init() {
        $this->rootEvent = Zend_Registry::get('rootEvent');
        $this->view->rootEvent = $this->rootEvent;
    }

    public function insertAction() {
        /* Multiple actors are possible but not multiple events because of different activity possibilities */
        $form = new Admin_Form_Involvement();
        $hierarchies = $form->addHierarchies('Involvement');
        $event = null;
        $actor = null;
        if ($this->_getParam('actorId')) {
            $actor = Model_EntityMapper::getById($this->_getParam('actorId'));
            $form->removeElement('actorIds');
        }
        if ($this->_getParam('eventId')) {
            $event = Model_EntityMapper::getById($this->_getParam('eventId'));
            $form->removeElement('eventButton');
            $form->removeElement('eventId');
        }
        $form->addActivity($event);
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            if ($actor) {
                $this->view->actor = $actor;
            } else {
                $this->view->actors = Model_EntityMapper::getByCodes('Actor');
            }
            if ($event) {
                $this->view->event = $event;
            } else {
                $this->view->events = Model_EntityMapper::getByCodes('Event');
            }
            $this->view->form = $form;
            $this->view->origin = $this->_getParam('origin');
            return;
        }
        if ($event && $event->class->code == 'E6') {
            $activity = Model_PropertyMapper::getByCode('P11');
        } else {
            $activity = Model_PropertyMapper::getById($this->_getParam('activity'));
        }
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        if ($actor) {
            $linkId = Model_LinkMapper::insert($activity->code, $event, $actor, $form->getValue('description'));
            self::save($linkId, $form, $hierarchies);
            Model_UserLogMapper::insert('link', $linkId, 'insert');
        } else {
            foreach (explode(",", $form->getValue('actorIds')) as $actorId) {
                $linkId = Model_LinkMapper::insert($activity->code, $event, $actorId, $form->getValue('description'));
                self::save($linkId, $form, $hierarchies);
                Model_UserLogMapper::insert('link', $linkId, 'insert');
            }
        }
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_insert');
        if ($this->_getParam('origin') == 'event') {
            return $this->_helper->redirector->gotoUrl('/admin/event/view/id/' . $event->id . '/#tabActor');
        }
        return $this->_helper->redirector->gotoUrl('/admin/actor/view/id/' . $actor->id . '/#tabEvent');
    }

    public function updateAction() {
        $link = Model_LinkMapper::getById($this->_getParam('id'));
        $actor = $link->range;
        $event = $link->domain;
        $form = new Admin_Form_Involvement();
        $hierarchies = $form->addHierarchies('Involvement', $link);
        $form->removeElement('actorIds');
        $form->removeElement('eventId');
        $form->removeElement('eventButton');
        $form->addActivity($event);
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $form->populateDates($link, ['OA5' => 'begin', 'OA6' => 'end']);
            $form->populate(['activity' => $link->property->id]);
            $form->populate(['description' => $link->description]);
            $this->view->actor = $actor;
            $this->view->event = $event;
            $this->view->form = $form;
            $this->view->origin = 'event';
            return;
        }
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        Model_LinkMapper::delete($link);
        if ($event->class->code == 'E6') {
            $activity = Model_PropertyMapper::getByCode('P11');
        } else {
            $activity = Model_PropertyMapper::getById($this->_getParam('activity'));
        }
        $linkId = Model_LinkMapper::insert($activity->code, $event, $actor, $form->getValue('description'));
        self::save($linkId, $form, $hierarchies);
        Model_UserLogMapper::insert('entity', $linkId, 'update');
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_update');
        if ($this->_getParam('origin') == 'event') {
            return $this->_helper->redirector->gotoUrl('/admin/event/view/id/' . $event->id . '/#tabActor');
        }
        return $this->_helper->redirector->gotoUrl('/admin/actor/view/id/' . $actor->id . '/#tabEvent');
    }

    private function save($linkId, Zend_Form $form, array $hierarchies) {
        foreach ($hierarchies as $hierarchy) {
            $idField = $hierarchy->nameClean . 'Id';
            if ($form->getValue($idField)) {
                foreach (explode(",", $form->getValue($idField)) as $rangeId) {
                    Model_LinkPropertyMapper::insert('P2', $linkId, $rangeId);
                }
            } else if ($hierarchy->system) {
                Model_LinkPropertyMapper::insert('P2', $linkId, $hierarchy->id);
            }
        }
        Model_DateMapper::saveLinkDates($linkId, $form);
    }

}
