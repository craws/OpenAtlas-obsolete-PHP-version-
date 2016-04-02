<?php

class ControllerTestCase extends Zend_Test_PHPUnit_ControllerTestCase {

    protected $testString = 'unitTestString';
    protected $defaultUsername = 'a';
    protected $defaultEmail = 'nobody@craws.net';
    protected $actorId = '1000';
    protected $sourceId = '1001';
    protected $objectId = '1002';
    protected $placeId = '1003';
    protected $eventId = '1004';
    protected $destructionId = '1005';
    protected $groupId = '1006';
    protected $biblioId = '1007';
    protected $source2Id = '1008';
    protected $carrierId = '1009';
    protected $subEventId = '1010';

    public function setUp() {
        $this->bootstrap = new Zend_Application(APPLICATION_ENV, ['config' => [
                APPLICATION_PATH . '/configs/application.ini',
                APPLICATION_PATH . '/configs/password.ini',
                APPLICATION_PATH . '/configs/model.ini'
        ]]);
        parent::setUp();
        $this->dbFixture();
        $this->assertTrue(true); // make an assertions to prevent marking of risky with strict mode
    }

    public function login() {
        $this->request->setMethod('POST')->setPost(['username' => $this->defaultUsername, 'password' => $this->defaultUsername]);
        $this->dispatch('admin/index/index');
        $this->resetRequest()->resetResponse();
    }

    public function loginTestUser() {
        $this->request->setMethod('POST')->setPost(['username' => 'testuser', 'password' => 'a']);
        $this->dispatch('admin/index/index');
        $this->resetRequest()->resetResponse();
    }

    private function dbFixture() {
        $db = Zend_DB_Table::getDefaultAdapter();
        $db->exec(file_get_contents('../data/install/structure.sql'));
        $db->exec(file_get_contents('../data/install/data_web.sql'));
        $db->exec(file_get_contents('../data/install/data_crm.sql'));
        $db->exec(file_get_contents('../data/install/data_node.sql'));
        $db->exec(file_get_contents('../data/install/data_test.sql'));
    }

}
