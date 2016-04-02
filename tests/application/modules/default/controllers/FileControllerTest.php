<?php

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
