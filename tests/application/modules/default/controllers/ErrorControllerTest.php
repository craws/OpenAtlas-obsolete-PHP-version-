<?php

class ErrorControllerTest extends ControllerTestCase {

    public function testFileNotFoundAction() {
        $this->dispatch('admin/index/ichglaubauch');
    }

    public function testErrorAction() {
        $this->dispatch('/default/nonono/error');
    }

}
