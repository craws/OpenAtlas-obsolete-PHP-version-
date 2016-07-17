<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_MemberController extends Zend_Controller_Action {

    public function insertAction() {
        $group = Model_EntityMapper::getById($this->_getParam('id'));
        $form = new Admin_Form_Member();
        $hierarchies = $form->addHierarchies('Member');
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->actors = Model_EntityMapper::getByCodes('Actor');
            $this->view->actor = $group;
            $this->view->form = $form;
            return;
        }
        foreach (explode(",", $form->getValue('relatedActorIds')) as $id) {
            $member = Model_EntityMapper::getById($id);
            $link = Model_LinkMapper::insert('P107', $group, $member, $this->_getParam('description'));
            self::save($link, $form, $hierarchies);
        }
        $this->_helper->message('info_insert');
        $url = '/admin/actor/view/id/' . $group->id . '/#tabMember';
        if ($form->getElement('continue')->getValue()) {
            $url = '/admin/member/insert/id/' . $group->id;
        }
        return $this->_helper->redirector->gotoUrl($url);
    }

    public function memberAction() {
        $member = Model_EntityMapper::getById($this->_getParam('id'));
        $form = new Admin_Form_Member();
        $hierarchies = $form->addHierarchies('Member');
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->actors = array_merge(
                Model_EntityMapper::getByCodes('Group'), Model_EntityMapper::getByCodes('LegalBody')
            );
            $this->view->actor = $member;
            $this->view->form = $form;
            return;
        }
        foreach (explode(",", $form->getValue('relatedActorIds')) as $id) {
            $group = Model_EntityMapper::getById($id);
            $link = Model_LinkMapper::insert('P107', $group, $member, $this->_getParam('description'));
            self::save($link, $form, $hierarchies);
        }
        $this->_helper->message('info_insert');
        $url = '/admin/actor/view/id/' . $member->id . '/#tabMemberOf';
        if ($form->getElement('continue')->getValue()) {
            $url = '/admin/member/member/id/' . $member->id;
        }
        return $this->_helper->redirector->gotoUrl($url);
    }

    public function updateAction() {
        $link = Model_LinkMapper::getById($this->_getParam('id'));
        $originActor = Model_EntityMapper::getById($this->_getParam('originActorId'));
        $actor = $link->domain;
        $relatedActor = $link->range;
        $form = new Admin_Form_Member();
        $hierarchies = $form->addHierarchies('Member', $link);
        $form->removeElement('relatedActorButton');
        $form->removeElement('relatedActorIds');
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $form->populateDates($link, ['OA5' => 'begin', 'OA6' => 'end']);
            $type = Model_LinkPropertyMapper::getLinkedEntity($link, 'P2');
            $node = Model_NodeMapper::getById($type->id);
            $form->populate(['typeId' => $node->id, 'description' => $link->description]);
            $this->view->actor = $originActor;
            $this->view->form = $form;
            $this->view->relatedActor = ($relatedActor->id == $originActor->id) ? $actor : $relatedActor;
            return;
        }
        $link->delete();
        $newLink = Model_LinkMapper::insert('P107', $actor, $relatedActor, $this->_getParam('description'));
        self::save($newLink, $form, $hierarchies);
        $this->_helper->message('info_update');
        $tab = ($originActor->id == $relatedActor->id) ? '#tabMemberOf' : '#tabMember';
        return $this->_helper->redirector->gotoUrl('/admin/actor/view/id/' . $originActor->id . $tab);
    }

    private function save(Model_Link $link, Zend_Form $form, array $hierarchies) {
        Model_LinkPropertyMapper::insertTypeLinks($link, $form, $hierarchies);
        Model_DateMapper::saveLinkDates($link, $form);
    }

}
