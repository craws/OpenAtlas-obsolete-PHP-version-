<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_ProfileControllerTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->login();
    }

    public function testIndex() {
        $this->dispatch('admin/profile');
        $this->request->setMethod('POST')->setPost([
            'theme' => 'default',
            'layout' => 'default',
            'language' => 1,
            'table_rows' => 20,

        ]);
        $this->dispatch('admin/profile');
    }

    public function testUpdate() {
        $this->dispatch('admin/profile/update');
        $this->request->setMethod('POST')->setPost(['language' => 1]);
        $this->dispatch('admin/profile/update');
        $this->login();
        $user = Zend_Registry::get('user');
        $user->getSetting('language');
        $this->dispatch('admin/profile/update');
        $this->request->setMethod('POST')->setPost(['language' => 1]);
        $this->dispatch('admin/profile/update');
        $this->request->setMethod('POST')->setPost(['email' => $this->defaultEmail]);
        $this->dispatch('admin/profile/update');
    }

    public function testPassword() {
        $this->dispatch('admin/profile/password');
        $formValues = [
            'passwordCurrent' => $this->defaultUsername,
            'password' => $this->testString,
            'passwordRetype' => $this->testString
        ];
        $this->request->setMethod('POST')->setPost($formValues);
        $this->dispatch('admin/profile/password');
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost($formValues);
        $this->dispatch('admin/profile/password'); //wrong password
        $this->resetRequest()->resetResponse();
        $formValues['passwordCurrent'] = $this->testString;
        $formValues['passwordRetype'] = 'wrong_retype';
        $this->request->setMethod('POST')->setPost($formValues);
        $this->dispatch('admin/profile/password');
    }

}
