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
    }

}
