<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_MemberController extends Zend_Controller_Action {

    public function insertAction() {
        $group = Model_EntityMapper::getById($this->_getParam('id'));
        $form = new Admin_Form_Member();
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->actors = Model_EntityMapper::getByCodes('Actor');
            $this->view->actor = $group;
            $this->view->form = $form;
            $this->view->typeTreeData = Model_NodeMapper::getTreeData('type', 'actor function');
            return;
        }
        $type = Model_NodeMapper::getRootType('type', 'actor function');
        if ($this->_getParam('typeId')) {
            $type = Model_EntityMapper::getById($this->_getParam('typeId'));
        }
        foreach (explode(",", $form->getValue('relatedActorIds')) as $id) {
            $member = Model_EntityMapper::getById($id);
            $link = Model_LinkMapper::insert('P107', $group, $member, $this->_getParam('description'));
            Model_LinkPropertyMapper::insert('P2', $link, $type);
            Model_DateMapper::saveLinkDates($link, $form);
        }
        $this->_helper->message('info_insert');
        // @codeCoverageIgnoreStart
        if ($form->getElement('continue')->getValue()) {
            return $this->_helper->redirector->gotoUrl('/admin/member/insert/id/' . $group->id);
        }
        // @codeCoverageIgnoreEnd
        return $this->_helper->redirector->gotoUrl('/admin/actor/view/id/' . $group->id . '/#tabMember');
    }

    public function memberAction() {
        $member = Model_EntityMapper::getById($this->_getParam('id'));
        $form = new Admin_Form_Member();
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->actors = array_merge(
                Model_EntityMapper::getByCodes('Group'),
                Model_EntityMapper::getByCodes('LegalBody')
            );
            $this->view->actor = $member;
            $this->view->form = $form;
            $this->view->typeTreeData = Model_NodeMapper::getTreeData('type', 'actor function');
            return;
        }
        $type = Model_NodeMapper::getRootType('type', 'actor function');
        if ($this->_getParam('typeId')) {
            $type = Model_EntityMapper::getById($this->_getParam('typeId'));
        }
        foreach (explode(",", $form->getValue('relatedActorIds')) as $id) {
            $group = Model_EntityMapper::getById($id);
            $link = Model_LinkMapper::insert('P107', $group, $member, $this->_getParam('description'));
            Model_LinkPropertyMapper::insert('P2', $link, $type);
            Model_DateMapper::saveLinkDates($link, $form);
        }
        $this->_helper->message('info_insert');
        // @codeCoverageIgnoreStart
        if ($form->getElement('continue')->getValue()) {
            return $this->_helper->redirector->gotoUrl('/admin/member/member/id/' . $member->id);
        }
        // @codeCoverageIgnoreEnd
        return $this->_helper->redirector->gotoUrl('/admin/actor/view/id/' . $member->id . '/#tabMemberOf');
    }

    public function updateAction() {
        $link = Model_LinkMapper::getById($this->_getParam('id'));
        $originActor = Model_EntityMapper::getById($this->_getParam('originActorId'));
        $actor = $link->getDomain();
        $relatedActor = $link->getRange();
        $form = new Admin_Form_Member();
        $form->removeElement('relatedActorButton');
        $form->removeElement('relatedActorIds');
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            Admin_Form_Abstract::populateDates($form, $link, ['OA5' => 'begin', 'OA6' => 'end']);
            $type = Model_LinkPropertyMapper::getLinkedEntity($link, 'P2');
            $form->populate(['typeId' => $type->id, 'description' => $link->description]);
            if ($type->rootId) {
                $form->populate(['typeButton' => $type->name]);
            }
            $this->view->actor = $originActor;
            $this->view->form = $form;
            $this->view->relatedActor = $relatedActor;
            $this->view->typeTreeData = Model_NodeMapper::getTreeData('type', 'actor function', $type);
            return;
        }
        $link->delete();
        $type = Model_NodeMapper::getRootType('type', 'actor function');
        if ($this->_getParam('typeId')) {
            $type = Model_EntityMapper::getById($this->_getParam('typeId'));
        }
        $newLink = Model_LinkMapper::insert('P107', $actor, $relatedActor, $this->_getParam('description'));
        Model_LinkPropertyMapper::insert('P2', $newLink, $type);
        Model_DateMapper::saveLinkDates($newLink, $form);
        $this->_helper->message('info_update');
        $tab = ($originActor->id == $relatedActor->id) ? '#tabMemberOf' : '#tabMember';
        return $this->_helper->redirector->gotoUrl('/admin/actor/view/id/' . $originActor->id . $tab);
    }

}
