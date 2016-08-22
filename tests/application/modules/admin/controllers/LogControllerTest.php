<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_LogControllerTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->login();
    }

    public function testIndex() {
        $this->dispatch('admin/log');
        $this->request->setMethod('POST')->setPost(['limit' => 0, 'priority' => 6, 'user_id' => 1]);
        $this->dispatch('admin/log');
        $this->assertController('log');
    }

    public function testViewDelete() {
        $this->assertTrue(Model_SettingsMapper::getSetting('general', 'log_level') < 8);
        $logId = Model_LogMapper::log('info', 'test', 'testview_and_long_text_for_code_coverage_and_so_on.');
        $this->request->setQuery(['id' => $logId]);
        $this->dispatch('admin/log/view');
        $this->request->setQuery(['id' => $logId]);
        $this->dispatch('admin/log/delete');
    }

    public function testDeleteAllFunction() {
        $this->dispatch('admin/log/delete-all');
    }

}
