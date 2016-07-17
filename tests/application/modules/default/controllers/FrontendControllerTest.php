<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class FrontendControllerTest extends ControllerTestCase {

    public function testFrontendAction() {
        /* since most sites in the frontend are static there are called here in one single test */
        $this->dispatch('offline');
        $this->login();
        $this->resetRequest()->resetResponse();
        $settings = Zend_Registry::get('settings');
        $settings['general']['offline'] = 0;
        Zend_Registry::set('settings', $settings);
        $this->dispatch('offline');
        $this->resetRequest()->resetResponse();
        $this->dispatch('/');
        $this->assertModule('default');
        $this->assertController('index');
        $this->assertResponseCode(200, 'Response code = ' . $this->getResponse()->getHttpResponseCode());
        $this->resetRequest()->resetResponse();
        $this->dispatch('default/contact');
        $this->dispatch('default/changelog');
        $this->dispatch('default/credits');
    }

}
