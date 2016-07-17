<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_OverviewControllerTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->login();
    }

    public function testOverviewSites() {
        $this->assertTrue(Zend_Auth::getInstance()->hasIdentity());
        Zend_Registry::get('user')->bookmarks = (Model_UserMapper::getBookmarks(Zend_Registry::get('user')->id));
        $this->dispatch('admin/overview');
        $this->assertController('overview');
        $this->dispatch('admin/overview/feedback');
        $this->dispatch('admin/overview/credits');
    }

    public function testModelAction() {
        $this->dispatch('admin/overview/model');
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost([
            'domain' => Model_ClassMapper::getByCode('E61')->id,
            'range' => Model_ClassMapper::getByCode('E61')->id,
            'property' => Model_PropertyMapper::getByCode('P20')->id
        ]);
        $this->dispatch('admin/overview/model');
    }

}
