<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_UserControllerTest extends ControllerTestCase {

    private $formValues;

    public function setUp() {
        parent::setUp();
        $this->login();
        $this->formValues = [
            'username' => $this->testString,
            'password' => $this->testString,
            'passwordRetype' => $this->testString,
            'email' => $this->defaultEmail,
            'realName' => '',
            'info' => '',
            'active' => 1,
            'group' => 1
        ];
    }

    public function testIndex() {
        $this->dispatch('admin/user');
    }

    public function testView() {
        $this->dispatch('admin/user/view/id/1');
    }

    public function testRandomPassword() {
        Model_User::randomPassword();
    }

    public function testLanguageShortform() {
        Model_LanguageMapper::getByShortform('en');
    }

    public function testCrud() {
        $this->dispatch('admin/user/insert');
        $this->dispatch('admin/user/insert');
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/user/insert');
        $this->resetRequest()->resetResponse();
        $this->formValues['email'] = 'non_existing_email@craws.net';
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/user/insert');
        $this->resetRequest()->resetResponse();
        $user1 = Model_UserMapper::getByUsername($this->testString);
        $this->assertTrue($user1->group == 'admin');
        $this->assertFalse($user1->group == 'StoaScheißerKoarl');
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/user/update/id/' . Zend_Registry::get('user')->id);
        $this->request->setMethod('POST')->setPost([
            'username' => $this->testString,
            'active' => 0,
            'group' => 1,
            'email' => ''
        ]);
        $this->dispatch('admin/user/update/id/' . $user1->id);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/user/delete/id/' . $user1->id);
        Model_UserMapper::getById('-1', false); // test prevent failure exception
    }

    public function testErrorPasswordRetype() {
        $this->formValues['passwordRetype'] = 'you shall not pass';
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/user/insert');
    }

    public function testErrorExistingUsername() {
        $this->formValues['username'] = $this->defaultUsername;
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/user/insert');
    }

    public function testJunk() {
        $this->assertFalse(Model_UserMapper::getByEmail($this->testString));
        $this->assertTrue(is_a(Model_UserMapper::getByEmail($this->defaultEmail), 'Model_User'));
    }

}
