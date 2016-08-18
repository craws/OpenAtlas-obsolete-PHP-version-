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

    public function testMiscellaneous() {
        // some tests for coverage - not elegant but preferred over cluttering code with IgnoreCoverage statements
        Model_ClassMapper::getByCode('non existing code');
        Model_PropertyMapper::getByCode('non existing code');
        \Craws\FilterInput::filter('whatever', 'non existing filter');
        Model_LogMapper::log('non existing priority', 'whatever', 'whatever');
        Model_GisMapper::getByEntity(Model_EntityMapper::getById(1));
        // test mail functions manually because mail is deactivated for testing
        $this->assertFalse(Model_UserMapper::getByEmail($this->testString));
        $this->assertTrue(is_a(Model_UserMapper::getByEmail($this->defaultEmail), 'Model_User'));
        // test date functions
        $date = new Zend_Date();
        Model_AbstractMapper::toZendDate($date);
        $date->setYear('-5000');
        Model_AbstractMapper::toDbDate($date);
    }


}
