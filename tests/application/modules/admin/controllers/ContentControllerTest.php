<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_ContentControllerTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->login();
    }

    public function testView() {
        $this->dispatch('admin/content/view/id/1');
    }

    public function testUpdate() {
        $this->dispatch('admin/content/index');
        $this->dispatch('admin/content/update/id/1');
        $this->request->setMethod('POST')->setPost(['intro' => ['de' => 'intro', 'en' => 'intro']]);
        $this->dispatch('admin/content/update/id/1');
    }

    public function testWrongParameter() {
        $this->dispatch('admin/content/update/itemId/0');
    }

}
