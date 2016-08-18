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
        $this->dispatch('about');
        $this->assertModule('default');
        $this->assertController('about');
        $this->assertResponseCode(200, 'Response code = ' . $this->getResponse()->getHttpResponseCode());
        $this->dispatch('/');
        $this->resetRequest()->resetResponse();
        $this->dispatch('contact');
        $this->resetRequest()->resetResponse();
        $this->dispatch('changelog');
        $this->resetRequest()->resetResponse();
        $this->dispatch('credits');
        $this->resetRequest()->resetResponse();
        $this->dispatch('class');
        $this->dispatch('class/view/id/' . Model_ClassMapper::getByCode('E1')->id);
        $this->resetRequest()->resetResponse();
        $this->dispatch('property');
        $this->dispatch('property/view/id/' . Model_PropertyMapper::getByCode('P2')->id);
    }

    public function testModelAction() {
        $this->login();
        $this->dispatch('model');
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost([
            'domain' => Model_ClassMapper::getByCode('E61')->id,
            'range' => Model_ClassMapper::getByCode('E61')->id,
            'property' => Model_PropertyMapper::getByCode('P20')->id
        ]);
        $this->dispatch('model');
    }

}
