<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_RelationController extends Zend_Controller_Action {

    public function insertAction() {
        $actor = Model_EntityMapper::getById($this->_getParam('id'));
        $form = new Admin_Form_Relation();
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->actors = Model_EntityMapper::getByCodes('Actor');
            $this->view->actor = $actor;
            $this->view->form = $form;
            $this->view->typeTreeData = Model_NodeMapper::getTreeData('type', 'actor actor relation');
            return;
        }
        $relation = Model_EntityMapper::getById($this->_getParam('typeId'));
        foreach (explode(",", $form->getValue('relatedActorIds')) as $id) {
            $relatedActor = Model_EntityMapper::getById($id);
            if ($form->getValue('inverse')) {
                $link = Model_LinkMapper::insert('OA7', $relatedActor, $actor, $this->_getParam('description'));
            } else {
                $link = Model_LinkMapper::insert('OA7', $actor, $relatedActor, $this->_getParam('description'));
            }
            Model_LinkPropertyMapper::insert('P2', $link, $relation);
            Model_DateMapper::saveLinkDates($link, $form);
        }
        $this->_helper->message('info_insert');
        // @codeCoverageIgnoreStart
        if ($form->getElement('continue')->getValue()) {
            return $this->_helper->redirector->gotoUrl('/admin/relation/insert/id/' . $actor->id);
        }
        // @codeCoverageIgnoreEnd
        return $this->_helper->redirector->gotoUrl('/admin/actor/view/id/' . $actor->id . '/#tabRelation');
    }

    public function updateAction() {
        $link = Model_LinkMapper::getById($this->_getParam('id'));
        $originActor = Model_EntityMapper::getById($this->_getParam('originActorId'));
        $form = new Admin_Form_Relation();
        $form->removeElement('relatedActorIds');
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            Admin_Form_Abstract::populateDates($form, $link, ['OA5' => 'begin', 'OA6' => 'end']);
            $type = Model_LinkPropertyMapper::getLinkedEntity($link, 'P2');
            $form->populate(['typeId' => $type->id, 'typeButton' => $type->name]);
            $form->populate(['description' => $link->description]);
            $form->populate(['inverse' => true]);
            if ($originActor->id == $link->getDomain()->id) {
                $form->populate(['inverse' => false]);
            }
            $this->view->actor = $originActor;
            $this->view->form = $form;
            $this->view->typeTreeData = Model_NodeMapper::getTreeData('type', 'actor actor relation', $type);
            return;
        }
        $actor = $link->getDomain();
        $relatedActor = $link->getRange();
        $link->delete();
        $relation = Model_EntityMapper::getById($this->_getParam('typeId'));
        $form->getValue('inverse');
        if (($originActor->id == $actor->id && !$form->getValue('inverse')) ||
            ($originActor->id != $actor->id && $form->getValue('inverse'))) {
            $link = Model_LinkMapper::insert('OA7', $actor, $relatedActor, $this->_getParam('description'));
        } else {
            $link = Model_LinkMapper::insert('OA7', $relatedActor, $actor, $this->_getParam('description'));
        }
        Model_LinkPropertyMapper::insert('P2', $link, $relation);
        Model_DateMapper::saveLinkDates($link, $form);
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/actor/view/id/' . $originActor->id . '/#tabRelation');
    }

}
