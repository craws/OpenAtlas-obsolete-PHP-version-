<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_HierarchyControllerTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->login();
    }

    public function testIndex() {
        $this->dispatch('admin/hierarchy');
    }

    public function testView() {
        $id = Model_NodeMapper::getByNodeCategoryName('Administrative Unit', 'Austria')->id;
        $this->dispatch('admin/hierarchy/view/id/' . $id);
        Model_NodeMapper::getByNodeCategoryName('You shall not be', 'found'); // test not found
    }

    public function testCrud() {
        $kindredship = Model_NodeMapper::getByNodeCategoryName('Actor Actor Relation', 'Parent of (Child of)');
        $this->request->setMethod('POST')->setPost([]);
        $this->dispatch('admin/hierarchy/insert/superId/' . $kindredship->superId);
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost(['name' => 'a new relation', 'inverse' => 'inverse']);
        $this->dispatch('admin/hierarchy/insert/superId/' . $kindredship->rootId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/hierarchy/update/id/' . $kindredship->id);
        $this->resetRequest()->resetResponse();
        $formValues = [
            'super' => $kindredship->superId,
            'name' => 'new name',
            'inverse' => 'whatever',
            'description' => 'description'
        ];
        $this->request->setMethod('POST')->setPost($formValues);
        $this->dispatch('admin/hierarchy/update/id/' . $kindredship->superId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/hierarchy/delete/id/' . $kindredship->superId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/hierarchy/delete/id/' . $kindredship->id);
        $this->resetRequest()->resetResponse();
        $relation = Model_NodeMapper::getByNodeCategoryName('Actor Actor Relation', 'Parent of (Child of)');
        $formValues['super'] = $relation->superId;
        $this->request->setMethod('POST')->setPost($formValues);
        // TODO this raises an: call to undefined method stdClass::update() in HierarchyController.php on line 94
        // $this->dispatch('admin/hierarchy/update/id/' . $relation->id);
        //$this->resetRequest()->resetResponse();
        $this->dispatch('admin/hierarchy/update/id/' . $relation->rootId); // test forbidden
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/hierarchy/delete/id/' . $relation->rootId); // test forbidden
    }

    public function testDeleteDenied() {
        $charter = Model_NodeMapper::getByNodeCategoryName('Source', 'Charter');
        $this->dispatch('admin/hierarchy/delete/id/' . $charter->id);
    }

}
