<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class ErrorControllerTest extends ControllerTestCase {

    public function testErrorAction() {
        $this->dispatch('admin/index/ichglaubauch'); // wrong action
        $this->resetRequest()->resetResponse();
        $this->dispatch('/default/nonono/error'); // wrong controller
        $this->resetRequest()->resetResponse();
        $this->login();
        $this->dispatch('admin/content/update/itemId/0'); // exception wrong id
    }

}
