<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_SettingsControllerTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->login();
    }

    public function testIndex() {
        $this->dispatch('admin/settings/index');
    }

    public function testUpdate() {
        $this->dispatch('admin/settings/update');
        $settingsPost = [];
        foreach (Model_SettingsMapper::getSettings() as $name => $value) {
            $settingsPost[$name] = $value;
        }
        $this->request->setMethod('POST')->setPost($settingsPost);
        $this->dispatch('admin/settings/update');
    }

}
