<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_SourceController extends Zend_Controller_Action {

    /* add sources to an entity */
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
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        foreach ($this->getRequest()->getPost() as $sourceId) {
            if (!Model_LinkMapper::linkExists('P67', $sourceId, $origin)) {
                Model_LinkMapper::insert('P67', $sourceId, $origin);
            }
        }
        Zend_Db_Table::getDefaultAdapter()->commit();
        return $this->_helper->redirector->gotoUrl('/admin/' . $controller . '/view/id/' . $origin->id . '/#tabSource');
    }

    /* Add entities to a source */
    public function add2Action() {
        $source = Model_EntityMapper::getById($this->_getParam('id'));
        $type = ucfirst($this->_getParam('type'));
        $entityType = ($type == 'Place') ? Model_EntityMapper::getByCodes('PhysicalObject') : Model_EntityMapper::getByCodes($type);
        if (!$this->getRequest()->isPost()) {
            $this->view->entities = $entityType;
            $this->view->type = $type;
            $this->view->source = $source;
            return;
        }
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        foreach ($this->getRequest()->getPost() as $entityId) {
            if (!Model_LinkMapper::linkExists('P67', $source->id, $entityId)) {
                Model_LinkMapper::insert('P67', $source->id, $entityId);
            }
        }
        Zend_Db_Table::getDefaultAdapter()->commit();
        return $this->_helper->redirector->gotoUrl('/admin/source/view/id/' . $source->id . '/#tab' . $type);
    }

    public function deleteAction() {
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        Model_EntityMapper::getById($this->_getParam('id'))->delete();
        Model_UserLogMapper::insert('entity', $this->_getParam('id'), 'delete');
        Zend_Db_Table::getDefaultAdapter()->commit();
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
        $hierarchies = $form->addHierarchies('Source');
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->form = $form;
            $this->view->event = $event;
            $this->view->actor = $actor;
            $this->view->object = $object;
            $this->view->typeTreeData = Model_NodeMapper::getTreeData('source');
            return;
        }
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        $sourceId = Model_EntityMapper::insert('E33', $form->getValue('name'), $form->getValue('description'));
        $source = Model_EntityMapper::getById($sourceId);
        $type = Model_NodeMapper::getByNodeCategoryName('Linguistic object classification', 'Source Content');
        Model_LinkMapper::insert('P2', $source, $type);
        self::save($form, $source, $hierarchies);
        if ($event) {
            Model_LinkMapper::insert('P67', $source, $event);
        }
        if ($actor) {
            Model_LinkMapper::insert('P67', $source, $actor);
        }
        if ($object) {
            Model_LinkMapper::insert('P67', $source, $object);
        }
        Model_UserLogMapper::insert('entity', $source->id, 'insert');
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_insert');
        $url = '/admin/source/view/id/' . $source->id;
        if ($form->getElement('continue')->getValue() && $event) {
            $url = '/admin/source/insert/eventId/' . $event->id;
        } else if ($form->getElement('continue')->getValue() && $actor) {
            $url = '/admin/source/insert/actorId/' . $actor->id;
        } else if ($form->getElement('continue')->getValue() && $object) {
            $url = '/admin/source/insert/objectId/' . $object->id;
        } else if ($form->getElement('continue')->getValue()) {
            $url = '/admin/source/insert';
        } else if ($event) {
            $url = '/admin/event/view/id/' . $event->id . '/#tabSource';
        } else if ($actor) {
            $url = '/admin/actor/view/id/' . $actor->id . '/#tabSource';
        } else if ($object) {
            $url = '/admin/place/view/id/' . $object->id . '/#tabSource';
        }
        return $this->_helper->redirector->gotoUrl($url);
    }

    public function textAddAction() {
        $source = Model_EntityMapper::getById($this->_getParam('id'));
        $form = new Admin_Form_Text();
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            $this->view->form = $form;
            $this->view->source = $source;
            return;
        }
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        $textId = Model_EntityMapper::insert('E33', $form->getValue('name'), $form->getValue('description'));
        Model_LinkMapper::insert('P2', $textId, Model_NodeMapper::getById($form->getValue('type')));
        Model_LinkMapper::insert('P73', $source, $textId);
        Model_UserLogMapper::insert('entity', $textId, 'insert');
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_insert');
        return $this->_helper->redirector->gotoUrl('/admin/source/view/id/' . $source->id . '#tabText');
    }

    public function textDeleteAction() {
        $link = Model_LinkMapper::getById($this->_getParam('linkId'));
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        $link->range->delete();
        Model_UserLogMapper::insert('link', $link->id, 'delete');
        Zend_Db_Table::getDefaultAdapter()->commit();
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
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        $text->update();
        $typeLink->delete();
        Model_LinkMapper::insert('P2', $text, Model_NodeMapper::getById($form->getValue('type')));
        Model_UserLogMapper::insert('entity', $text->id, 'update');
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/source/view/id/' . $source->id . '#tabText');
    }

    public function updateAction() {
        $source = Model_EntityMapper::getById($this->_getParam('id'));
        $form = new Admin_Form_Source();
        $hierarchies = $form->addHierarchies('Source', $source);
        $this->view->form = $form;
        $this->view->source = $source;
        if (!$this->getRequest()->isPost()) {
            $form->populate([
                'class' => $source->class->id,
                'name' => $source->name,
                'description' => $source->description,
                'modified' => ($source->modified) ? $source->modified->getTimestamp() : 0
            ]);
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
        Zend_Db_Table::getDefaultAdapter()->beginTransaction();
        $source->update();
        foreach (Model_LinkMapper::getLinks($source, 'P2') as $link) {
            if ($link->range->name != "Source Content") {
                $link->delete();
            }
        }
        self::save($form, $source, $hierarchies);
        Model_UserLogMapper::insert('entity', $source->id, 'update');
        Zend_Db_Table::getDefaultAdapter()->commit();
        $this->_helper->message('info_update');
        return $this->_helper->redirector->gotoUrl('/admin/source/view/id/' . $source->id);
    }

    private function save(Zend_Form $form, Model_Entity $entity, array $hierarchies) {
        Model_LinkMapper::insertTypeLinks($entity, $form, $hierarchies);
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
    }

}
