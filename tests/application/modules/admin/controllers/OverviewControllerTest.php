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
        $this->dispatch('admin/overview/network');
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost(['E18_color' => '#000', 'E18' => 1, 'P7' => 1, 'show-orphans' => 1]);
        $this->dispatch('admin/overview/network');
    }

}
