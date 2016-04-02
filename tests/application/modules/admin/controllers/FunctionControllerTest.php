<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_FunctionControllerTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->login();
    }

    public function testAddField() {
        $this->request->setMethod('POST')->setPost(['fieldName' => 'alias', 'elementId' => $this->actorId]);
        $this->dispatch('admin/function/add-field');
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost(['fieldName' => 'eventType', 'elementId' => $this->eventId]);
        $this->dispatch('admin/function/add-field');
    }

    public function testUnlink() {
        $document = Model_EntityMapper::getById($this->sourceId);
        $link = Model_LinkMapper::getLink($document, 'P2');
        $this->dispatch('admin/function/unlink/id/' . $link->id . '/entityId/' . $document->id);
    }

    public function testBookmark() {
        Zend_Registry::get('user')->bookmarks = (Model_UserMapper::getBookmarks(Zend_Registry::get('user')->id));
        $this->request->setMethod('POST')->setPost(['entityId' => $this->sourceId]);
        $this->dispatch('admin/function/bookmark');
    }
}
