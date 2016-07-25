<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_InvolvementController extends Zend_Controller_Action {

    public function init() {
        $this->rootEvent = Zend_Registry::get('rootEvent');
        $this->view->rootEvent = $this->rootEvent;
    }

    public function insertAction() {
        /* Only multiple actors. Multiple events not viable because of different activity possiblities */
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
        if ($actor) {
            $link = Model_LinkMapper::insert($activity->code, $event, $actor, $form->getValue('description'));
            self::save($link, $form, $hierarchies);
        } else {
            foreach (explode(",", $form->getValue('actorIds')) as $actorId) {
                $link = Model_LinkMapper::insert($activity->code, $event, $actorId, $form->getValue('description'));
                self::save($link, $form, $hierarchies);
            }
        }
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
        Model_LinkMapper::delete($link);
        if ($event->class->code == 'E6') {
            $activity = Model_PropertyMapper::getByCode('P11');
        } else {
            $activity = Model_PropertyMapper::getById($this->_getParam('activity'));
        }
        $newLink = Model_LinkMapper::insert($activity->code, $event, $actor, $form->getValue('description'));
        self::save($newLink, $form, $hierarchies);
        $this->_helper->message('info_update');
        if ($this->_getParam('origin') == 'event') {
            return $this->_helper->redirector->gotoUrl('/admin/event/view/id/' . $event->id . '/#tabActor');
        }
        return $this->_helper->redirector->gotoUrl('/admin/actor/view/id/' . $actor->id . '/#tabEvent');
    }

    private function save(Model_Link $link, Zend_Form $form, array $hierarchies) {
        foreach ($hierarchies as $hierarchy) {
            $idField = $hierarchy->nameClean . 'Id';
            if ($form->getValue($idField)) {
                foreach (explode(",", $form->getValue($idField)) as $id) {
                    Model_LinkPropertyMapper::insert('P2', $link, Model_NodeMapper::getById($id));
                }
            } else if ($hierarchy->system) {
                Model_LinkPropertyMapper::insert('P2', $link, $hierarchy);
            }
        }
        Model_DateMapper::saveLinkDates($link, $form);
    }

}
