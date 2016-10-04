<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_IndexControllerTest extends ControllerTestCase {

    public function testLoginWithWrongUsername() {
        $this->request->setMethod('POST')->setPost(['username' => 'You shall not', 'password' => 'pass']);
        $this->dispatch('admin/index/index');
        $this->assertFalse(Zend_Auth::getInstance()->hasIdentity());
    }

    public function testLoginWithWrongPassword() {
        $this->dispatch('admin/index/index');
        $this->assertFalse(Zend_Auth::getInstance()->hasIdentity());
    }

    public function testLoginLogout() {
        $this->login();
        $this->assertTrue(Zend_Auth::getInstance()->hasIdentity());
        $this->dispatch('admin/index/index'); // test redirect to overview if already logged in
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/index/logout');
        $this->assertFalse(Zend_Auth::getInstance()->hasIdentity());
        $this->resetRequest()->resetResponse();
        $this->loginInactiveUser();
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/index/logout');
        $this->request->setMethod('POST')->setPost(['username' => $this->defaultUsername, 'password' => 'wrong password']);
        $this->dispatch('admin/index/index');
        $this->login();
        $this->dispatch('admin/index/index');
    }

    public function testRedirect() {
        $this->dispatch('admin/index/index');
        $this->dispatch('admin/log/');
        $this->login();
        $this->dispatch('admin/index/index');
    }

    public function testForbidden() {
        $this->loginTestUser();
        $this->assertTrue(Zend_Auth::getInstance()->hasIdentity());
        $this->dispatch('admin/user');
    }

    public function testLoginTries() {
        $this->assertFalse(Model_User::loginAttemptsExceeded(Model_UserMapper::getByUsername($this->defaultUsername)));
        for ($i = 0; $i < Model_SettingsMapper::getSetting('failed_login_tries') + 1; $i++) {
            $this->request->setMethod('POST')->setPost([
                'username' => $this->defaultUsername,
                'password' => 'wrong password'
            ]);
            $this->dispatch('admin/index/index');
            $this->resetRequest()->resetResponse();
        }
        $this->assertTrue(Model_User::loginAttemptsExceeded(Model_UserMapper::getByUsername($this->defaultUsername)));
    }

    public function testPasswordReset() {
        $this->dispatch('admin/index/password-reset');
        $this->dispatch('admin/index/reset-confirm');
        $settings = Zend_Registry::get('settings');
        $settings['mail'] = 1;
        Zend_Registry::set('settings', $settings);
        $this->request->setMethod('POST')->setPost(['email' => $this->defaultEmail]);
        $this->dispatch('admin/index/password-reset');
        $this->request->setMethod('POST')->setPost(['email' => "non_existing_email@norepy.org"]);
        $this->dispatch('admin/index/password-reset');
        $this->dispatch('admin/index/reset-confirm');
    }

}
