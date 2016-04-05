<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_SourceControllerTest extends ControllerTestCase {

    private $formValues = [
        'name' => 'source',
        'description' => 'description'
    ];

    public function setUp() {
        parent::setUp();
        $this->login();
        $type = Model_NodeMapper::getByNodeCategoryName('type', 'source', 'letter');
        $this->formValues['typeId'] = $type->id;
        $this->formValues['typeButton'] = $type->name;
    }

    public function testAdd() {
        $this->dispatch('admin/source/add/id/' . $this->eventId);
    }

    public function testCrud() {
        $this->dispatch('admin/source/insert/code/E33');
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/source/insert/code/E33/eventId/' . $this->eventId);
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/source/insert/code/E33/actorId/' . $this->actorId);
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/source/insert/code/E33/objectId/' . $this->placeId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/source');
        $this->dispatch('admin/source/update/id/' . $this->sourceId);
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/source/update/id/' . $this->sourceId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/source/delete/id/' . $this->sourceId);
    }

    public function testLink() {
        $this->dispatch('admin/source/link/sourceId/' . $this->sourceId . '/rangeId/' . $this->actorId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/source/link/sourceId/' . $this->sourceId . '/rangeId/' . $this->actorId); // test existing
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/source/link/sourceId/' . $this->sourceId . '/rangeId/' . $this->eventId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/source/view/id/' . $this->sourceId);
    }

    public function testText() {
        $original = Model_NodeMapper::getByNodeCategoryName(
            'type',
            'Linguistic object classification',
            'Source Original Text'
        );
        $this->dispatch('admin/source/text-add/id/' . $this->sourceId);
        $this->resetRequest()->resetResponse();
        $formValues = ['type' => $original->id, 'name' => 'original', 'description' => 'description'];
        $this->request->setMethod('POST')->setPost($formValues);
        $this->dispatch('admin/source/text-add/id/' . $this->sourceId);
        $this->resetRequest()->resetResponse();
        $textLink = Model_LinkMapper::getLink($this->sourceId, 'P73');
        $this->dispatch('admin/source/text-update/linkId/' . $textLink->id);
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost($formValues);
        $this->dispatch('admin/source/text-update/linkId/' . $textLink->id);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/source/text-delete/linkId/' . $textLink->id);
    }

}
