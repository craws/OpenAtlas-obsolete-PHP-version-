<?php

class IndexControllerTest extends ControllerTestCase {

    public function testDefaultIndexAction() {
        $this->dispatch('/offline');
        $this->login();
        $this->dispatch('/');
        $this->assertModule('default');
        $this->assertController('index');
        $this->assertResponseCode(200, 'Response code = ' . $this->getResponse()->getHttpResponseCode());
    }

}
