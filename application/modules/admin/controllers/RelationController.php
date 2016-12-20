<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_RelationController extends Zend_Controller_Action {

    public function insertAction() {
        $actor = Model_EntityMapper::getById($this->_getParam('id'));
        $form = new Admin_Form_Relation();
        $hierarchies = $form->addHierarchies('Actor Actor Relation');
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->actors = Model_EntityMapper::getByCodes('Actor');
            $this->view->actor = $actor;
            $this->view->form = $form;
            return;
        }
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        foreach (explode(",", $form->getValue('relatedActorIds')) as $relatedActorId) {
            if ($form->getValue('inverse')) {
                $linkId = Model_LinkMapper::insert('OA7', $relatedActorId, $actor, $this->_getParam('description'));
            } else {
                $linkId = Model_LinkMapper::insert('OA7', $actor, $relatedActorId, $this->_getParam('description'));
            }
            self::save($linkId, $form, $hierarchies);
            Model_UserLogMapper::insert('link', $linkId, 'insert');
        }
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_insert');
        $url = '/admin/actor/view/id/' . $actor->id . '/#tabRelation';
        if ($form->getElement('continue')->getValue()) {
            $url = '/admin/relation/insert/id/' . $actor->id;
        }
        return $this->_helper->redirector->gotoUrl($url);
    }

    public function updateAction() {
        $link = Model_LinkMapper::getById($this->_getParam('id'));
        $originActor = Model_EntityMapper::getById($this->_getParam('originActorId'));
        $form = new Admin_Form_Relation();
        $hierarchies = $form->addHierarchies('Actor Actor Relation', $link);
        $form->removeElement('relatedActorIds');
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $form->populateDates($link, ['OA5' => 'begin', 'OA6' => 'end']);
            $form->populate(['typeId' => $link->type->id]);
            $form->populate(['description' => $link->description]);
            $form->populate(['inverse' => true]);
            if ($originActor->id == $link->domain->id) {
                $form->populate(['inverse' => false]);
            }
            $this->view->actor = $originActor;
            $this->view->form = $form;
            return;
        }
        $actor = $link->domain;
        $relatedActor = $link->range;
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        $link->delete();
        $form->getValue('inverse');
        if (($originActor->id == $actor->id && !$form->getValue('inverse')) ||
            ($originActor->id != $actor->id && $form->getValue('inverse'))) {
            $linkId = $actor->link('OA7', $relatedActor, $this->_getParam('description'));
        } else {
            $linkId = $relatedActor->link('OA7', $actor, $this->_getParam('description'));
        }
        self::save($linkId, $form, $hierarchies);
        Model_UserLogMapper::insert('link', $linkId, 'update');
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/actor/view/id/' . $originActor->id . '/#tabRelation');
    }

    private function save($linkId, Zend_Form $form, array $hierarchies) {
        Model_LinkPropertyMapper::insertTypeLinks($linkId, $form, $hierarchies);
        Model_DateMapper::saveLinkDates($linkId, $form);
    }

}
