<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_EventControllerTest extends ControllerTestCase {

    private $formValues = [
        'name' => 'Event Horizon',
        'description' => 'Never look back',
        'beginYear' => '23',
        'beginMonth' => '12',
        'beginDay' => '23',
        'beginComment' => 'comment',
    ];

    public function setUp() {
        parent::setUp();
        $this->login();
        $actor = Model_EntityMapper::getById($this->actorId);
        $this->formValues['superId'] = $this->eventId;
        $this->formValues['typeId'] = Model_NodeMapper::getByNodeCategoryName('Event', 'Conflict')->id;
        $this->formValues['placeId'] = $this->objectId;
        $this->formValues['recipientId'] = $actor->id;
        $this->formValues['recipientButton'] = $actor->name;
        $this->formValues['donorId'] = $actor->id;
        $this->formValues['donorButton'] = $actor->name;
        $this->formValues['acquisitionPlaceId'] = $this->objectId;
        $this->formValues['acquisitionPlaceButton'] = $this->objectId;
    }

    public function testIndex() {
        $this->dispatch('admin/event');
    }

    public function testView() {
        $this->dispatch('admin/event/view/id/' . $this->eventId);
    }

    public function testAdd() {
        $this->dispatch('admin/event/add/id/' . $this->sourceId);
    }

    public function testLink() {
        $this->dispatch('admin/event/link/eventId/' . $this->eventId . '/rangeId/' . $this->sourceId); // test existing
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/event/link/eventId/' . $this->eventId . '/rangeId/' . $this->source2Id);
    }


    public function testCrud() {
        $this->dispatch('admin/event/insert'); // test errror if code is missing
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/event/insert/code/E8');
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/event/insert/code/E8/sourceId/' . $this->sourceId);
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/event/insert/code/E8/actorId/' . $this->actorId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/event/update/id/' . $this->eventId);
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/event/update/id/' . $this->eventId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/event/update/id/' . $this->subEventId);
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/event/update/id/' . $this->destructionId);
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost([]);
        $this->dispatch('admin/event/update/id/' . $this->eventId);
        $this->dispatch('admin/event/delete/id/' . $this->eventId);
    }

}
