<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_InvolvementController extends Zend_Controller_Action {

    public function insertAction() {
        /* only multiple actors, multiple events not viable because of different activity possiblities */
        $form = new Admin_Form_Involvement();
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
            $this->view->origin = $this->_getParam('origin');
            $this->view->form = $form;
            $this->view->involvementTreeData = Model_NodeMapper::getTreeData('type', 'involvement');
            return;
        }
        if ($event && $event->getClass()->code == 'E6') {
            $activity = Model_PropertyMapper::getByCode('P11');
        } else {
            $activity = Model_PropertyMapper::getById($this->_getParam('activity'));
        }
        if ($actor) {
            $link = Model_LinkMapper::insert($activity->code, $event, $actor, $form->getValue('description'));
            Model_DateMapper::saveLinkDates($link, $form);
            if ($this->_getParam('involvementId')) {
                Model_LinkPropertyMapper::insert('P2', $link, Model_EntityMapper::getById($this->_getParam('involvementId')));
            }
        } else {
            foreach (explode(",", $form->getValue('actorIds')) as $id) {
                $actor = Model_EntityMapper::getById($id);
                $link = Model_LinkMapper::insert($activity->code, $event, $actor, $form->getValue('description'));
                Model_DateMapper::saveLinkDates($link, $form);
                if ($this->_getParam('involvementId')) {
                    Model_LinkPropertyMapper::insert('P2', $link, Model_EntityMapper::getById($this->_getParam('involvementId')));
                }
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
        $actor = $link->getRange();
        $event = $link->getDomain();
        $form = new Admin_Form_Involvement();
        $form->removeElement('actorIds');
        $form->removeElement('eventId');
        $form->removeElement('eventButton');
        $form->addActivity($event);
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            Admin_Form_Abstract::populateDates($form, $link, ['OA5' => 'begin', 'OA6' => 'end']);
            $form->populate(['activity' => $link->getProperty()->id]);
            $form->populate(['description' => $link->description]);
            $involvement = Model_LinkPropertyMapper::getLinkedEntity($link, 'P2');
            if ($involvement) {
                $form->populate(['involvementId' => $involvement->id, 'involvementButton' => $involvement->name]);
            }
            $this->view->involvementTreeData = Model_NodeMapper::getTreeData('type', 'involvement', $involvement);
            $this->view->actor = $actor;
            $this->view->event = $event;
            $this->view->origin = 'event';
            $this->view->form = $form;
            return;
        }
        Model_LinkMapper::delete($link);
        if ($event->getClass()->code == 'E6') {
            $activity = Model_PropertyMapper::getByCode('P11');
        } else {
            $activity = Model_PropertyMapper::getById($this->_getParam('activity'));
        }
        $newLink = Model_LinkMapper::insert($activity->code, $event, $actor, $form->getValue('description'));
        if ($this->_getParam('involvementId')) {
            Model_LinkPropertyMapper::insert('P2', $newLink, Model_EntityMapper::getById($this->_getParam('involvementId')));
        }
        Model_DateMapper::saveLinkDates($newLink, $form);
        $this->_helper->message('info_update');
        // @codeCoverageIgnoreStart
        if ($this->_getParam('origin') == 'event') {
            return $this->_helper->redirector->gotoUrl('/admin/event/view/id/' . $event->id . '/#tabActor');
        }
        return $this->_helper->redirector->gotoUrl('/admin/actor/view/id/' . $actor->id . '/#tabEvent');
        // @codeCoverageIgnoreEnd
    }

}
