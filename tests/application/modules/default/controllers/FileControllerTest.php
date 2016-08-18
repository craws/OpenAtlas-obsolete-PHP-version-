<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class FileControllerTest extends ControllerTestCase {

    public function testViewAction() {
        $this->login();
        $this->dispatch('/');
        $this->resetRequest()->resetResponse();
        $this->dispatch('/file/view/file/schema');
        $this->resetRequest()->resetResponse();
        $this->dispatch('/file/view/file/whatever');
    }

}
