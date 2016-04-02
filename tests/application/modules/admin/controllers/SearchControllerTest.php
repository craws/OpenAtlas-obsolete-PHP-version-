<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_SearchControllerTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->login();
    }

    public function testIndex() {
        $this->request->setMethod('POST')->setPost(['term' => 'a', 'searchOwn' => 1, 'searchDescription' => 1]);
        $this->dispatch('admin/search');
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost(['term' => 'a', 'searchOwn' => 0, 'searchDescription' => 1]);
        $this->dispatch('admin/search');    }

}
