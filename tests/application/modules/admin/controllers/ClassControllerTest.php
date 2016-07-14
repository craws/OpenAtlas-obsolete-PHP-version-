<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_ClassControllerTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->login();
    }

    public function testIndex() {
        $this->dispatch('admin/class');
    }

    public function testView() {
        $this->dispatch('admin/class/view/id/1');
        // some tests for coverage
        Model_ClassMapper::getByCode('007'); // test non existing code
        Model_PropertyMapper::getByCode('007'); // test non existing code
        Model_NodeMapper::getHierarchyByName('007'); // test non existing hierarchy name
    }

}
