<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_SourceController extends Zend_Controller_Action {

    public function addAction() {
        $origin = Model_EntityMapper::getById($this->_getParam('id'));
        $array = Zend_Registry::get('config')->get('codeView')->toArray();
        $controller = $array[$origin->class->code];
        if (!$this->getRequest()->isPost()) {
            $this->view->menuHighlight = $controller;
            $this->view->controller = $controller;
            $this->view->origin = $origin;
            $this->view->sources = Model_EntityMapper::getByCodes('Source', 'Source Content');
            return;
        }
        foreach ($this->getRequest()->getPost() as $sourceId) {
            $source = Model_EntityMapper::getById((int) $sourceId);
            if (!Model_LinkMapper::linkExists('P67', $source, $origin)) {
                Model_LinkMapper::insert('P67', $source, $origin);
            }
        }
        return $this->_helper->redirector->gotoUrl('/admin/' . $controller . '/view/id/' . $origin->id . '/#tabSource');
    }

    public function deleteAction() {
        Model_EntityMapper::getById($this->_getParam('id'))->delete();
        $this->_helper->message('info_delete');
        return $this->_helper->redirector->gotoUrl('/admin/source');
    }

    public function indexAction() {
        $this->view->sources = Model_EntityMapper::getByCodes('Source', 'Source Content');
        $this->view->places = [];
    }

    public function insertAction() {
        $event = null;
        if ($this->_getParam('eventId')) {
            $event = Model_EntityMapper::getById($this->_getParam('eventId'));
            $this->view->menuHighlight = 'event';
        }
        $actor = null;
        if ($this->_getParam('actorId')) {
            $actor = Model_EntityMapper::getById($this->_getParam('actorId'));
            $this->view->menuHighlight = 'actor';
        }
        $object = null;
        if ($this->_getParam('objectId')) {
            $object = Model_EntityMapper::getById($this->_getParam('objectId'));
            $this->view->menuHighlight = 'place';
        }
        $form = new Admin_Form_Source();
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->form = $form;
            $this->view->event = $event;
            $this->view->actor = $actor;
            $this->view->object = $object;
            $this->view->typeTreeData = Model_NodeMapper::getTreeData('source');
            return;
        }
        $source = Model_EntityMapper::insert('E33', $form->getValue('name'), $form->getValue('description'));
        $type = Model_NodeMapper::getByNodeCategoryName('Linguistic object classification', 'Source Content');
        Model_LinkMapper::insert('P2', $source, $type);
        self::save($form, $source);
        if ($event) {
            Model_LinkMapper::insert('P67', $source, $event);
        }
        if ($actor) {
            Model_LinkMapper::insert('P67', $source, $actor);
        }
        if ($object) {
            Model_LinkMapper::insert('P67', $source, $object);
        }
        $this->_helper->message('info_insert');
        // @codeCoverageIgnoreStart
        if ($form->getElement('continue')->getValue() && $event) {
            return $this->_helper->redirector->gotoUrl('/admin/source/insert/eventId/' . $event->id);
        }
        if ($form->getElement('continue')->getValue() && $actor) {
            return $this->_helper->redirector->gotoUrl('/admin/source/insert/actorId/' . $actor->id);
        }
        if ($form->getElement('continue')->getValue() && $object) {
            return $this->_helper->redirector->gotoUrl('/admin/source/insert/objectId/' . $object->id);
        }
        if ($form->getElement('continue')->getValue()) {
            return $this->_helper->redirector->gotoUrl('/admin/source/insert');
        }
        if ($event) {
            return $this->_helper->redirector->gotoUrl('/admin/event/view/id/' . $event->id . '/#tabSource');
        }
        if ($actor) {
            return $this->_helper->redirector->gotoUrl('/admin/actor/view/id/' . $actor->id . '/#tabSource');
        }
        if ($object) {
            return $this->_helper->redirector->gotoUrl('/admin/place/view/id/' . $object->id . '/#tabSource');
        }
        return $this->_helper->redirector->gotoUrl('/admin/source/view/id/' . $source->id);
        // @codeCoverageIgnoreEnd
    }

    public function textAddAction() {
        $source = Model_EntityMapper::getById($this->_getParam('id'));
        $form = new Admin_Form_Text();
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->form = $form;
            $this->view->source = $source;
            return;
        }
        $text = Model_EntityMapper::insert('E33', $form->getValue('name'), $form->getValue('description'));
        Model_LinkMapper::insert('P2', $text, Model_EntityMapper::getById($form->getValue('type')));
        Model_LinkMapper::insert('P73', $source, $text);
        $this->_helper->message('info_insert');
        return $this->_helper->redirector->gotoUrl('/admin/source/view/id/' . $source->id . '#tabText');
    }

    public function textDeleteAction() {
        $link = Model_LinkMapper::getById($this->_getParam('linkId'));
        $link->range->delete();
        $this->_helper->message('info_delete');
        return $this->_helper->redirector->gotoUrl('/admin/source/view/id/' . $link->domain->id . '#tabText');
    }

    public function textUpdateAction() {
        $link = Model_LinkMapper::getById($this->_getParam('linkId'));
        $text = Model_EntityMapper::getById($link->range->id);
        $source = Model_EntityMapper::getById($link->domain->id);
        $form = new Admin_Form_Text();
        foreach (Model_LinkMapper::getLinks($text, 'P2') as $link) {
            if (array_key_exists($link->range->id, $form->getElement('type')->getMultiOptions())) {
                $typeLink = $link;
            }
        }
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $form->populate(['type' => $typeLink->range->id, 'name' => $text->name, 'description' => $text->description]);
            $this->view->text = $text;
            $this->view->source = $source;
            $this->view->form = $form;
            return;
        }
        $text->name = $form->getValue('name');
        $text->description = $form->getValue('description');
        $text->update();
        $typeLink->delete();
        Model_LinkMapper::insert('P2', $text, Model_EntityMapper::getById($form->getValue('type')));
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/source/view/id/' . $source->id . '#tabText');
    }

    public function updateAction() {
        $source = Model_EntityMapper::getById($this->_getParam('id'));
        $form = new Admin_Form_Source();
        $this->view->form = $form;
        $this->view->source = $source;
        if (!$this->getRequest()->isPost()) {
            $form->populate([
                'class' => $source->class->id,
                'name' => $source->name,
                'description' => $source->description,
                'modified' => ($source->modified) ? $source->modified->getTimestamp() : 0
            ]);
            $type = Model_NodeMapper::getNodeByEntity('Source', $source);
            if ($type && $type->rootId) {
                $form->populate(['typeId' => $type->id, 'typeButton' => $type->name]);
                $this->view->type = $type;
            }
            $this->view->typeTreeData = Model_NodeMapper::getTreeData('source', [$type]);
            return;
        }
        $formValid = $form->isValid($this->getRequest()->getPost());
        $modified = Model_EntityMapper::checkIfModified($source, $form->modified->getValue());
        // @codeCoverageIgnoreStart
        if ($modified) {
            $log = Model_UserLogMapper::getLogForView('entity', $source->id);
            $this->view->modifier = $log['modifier_name'];
        }
        // @codeCoverageIgnoreEnd
        if (!$formValid || $modified) {
            $this->_helper->message('error_modified');
            $this->view->typeTreeData = Model_NodeMapper::getTreeData('source');
            return;
        }
        $source->name = $form->getValue('name');
        $source->description = $form->getValue('description');
        $source->update();
        foreach (Model_LinkMapper::getLinks($source, 'P2') as $link) {
            if ($link->range->name != "Source Content") {
                $link->delete();
            }
        }
        self::save($form, $source);
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/source/view/id/' . $source->id);
    }

    private function save(Zend_Form $form, Model_Entity $source) {
        $sourceType = Model_NodeMapper::getRootType('source');
        if ($this->_getParam('typeId')) {
            $sourceType = Model_NodeMapper::getById($this->_getParam('typeId'));
        };
        Model_LinkMapper::insert('P2', $source, $sourceType);
    }

    public function viewAction() {
        $source = Model_EntityMapper::getById($this->_getParam('id'));
        $this->view->actorLinks = [];
        $this->view->eventLinks = [];
        $this->view->placeLinks = [];
        foreach (Model_LinkMapper::getLinks($source, 'P67') as $link) {
            $code = $link->range->class->code;
            if ($code == 'E18') {
                $this->view->placeLinks[] = $link;
            } else if (in_array($code, Zend_Registry::get('config')->get('codeEvent')->toArray())) {
                $this->view->eventLinks[] = $link;
            } else if (in_array($code, Zend_Registry::get('config')->get('codeActor')->toArray())) {
                $this->view->actorLinks[] = $link;
            }
        }
        $referenceLinks = [];
        foreach (Model_LinkMapper::getLinks($source, 'P67', true) as $link) {
            switch ($link->domain->class->code) {
                case 'E31':
                    $referenceLinks[] = $link;
                    break;
            }
        }
        foreach (Model_LinkMapper::getLinks($source, 'P128', true) as $link) {
            $referenceLinks[] = $link;
        }
        $this->view->referenceLinks = $referenceLinks;
        $this->view->source = $source;
        $this->view->textLinks = Model_LinkMapper::getLinks($source, 'P73');
        $this->view->sourceType = Model_NodeMapper::getNodeByEntity('Source', $source);
    }

}
