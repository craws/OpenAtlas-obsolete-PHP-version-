<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_PropertyControllerTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->login();
    }

    public function testIndex() {
        $this->dispatch('admin/property');
    }

    public function testCrud() {
        $this->dispatch('admin/property/insert');
        $this->request->setMethod('POST')->setPost(['code' => $this->testString, 'name' => $this->testString]);
        $this->dispatch('admin/property/insert');
        $this->dispatch('admin/property/view/id/1');
        $this->request->setMethod('POST')->setPost(['code' => $this->testString, 'name' => $this->testString]);
        $this->dispatch('admin/property/update');
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/property/update/id/1');
        $this->dispatch('admin/property/delete/id/1');
    }

}
