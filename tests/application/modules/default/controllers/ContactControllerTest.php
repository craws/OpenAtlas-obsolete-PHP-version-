<?php

class ContactControllerTest extends ControllerTestCase {

    public function testContactIndexAction() {
        $this->login();
        $this->dispatch('/default/contact/');
        $this->assertModule('default');
        $this->assertController('contact');
        $this->assertResponseCode(200, 'Response code = ' . $this->getResponse()->getHttpResponseCode());
    }

}
