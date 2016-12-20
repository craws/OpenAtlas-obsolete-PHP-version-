<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_InvolvementControllerTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->login();
    }

    public function testCrudInvolvement() {
        $formValues = [
            'activity' => Model_PropertyMapper::getByCode('P11')->id,
            'involvementId' => Model_NodeMapper::getByNodeCategoryName('Involvement', 'Creator')->id,
        ];
        $this->dispatch('admin/involvement/insert/origin/actor/actorId/' . $this->actorId);
        $this->resetRequest()->resetResponse();
        $formValues['eventId'] = $this->eventId;
        $this->request->setMethod('POST')->setPost($formValues);
        $this->dispatch('admin/involvement/insert/origin/actor/actorId/' . $this->actorId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/involvement/insert/origin/event/eventId/' . $this->eventId);
        $this->resetRequest()->resetResponse();
        $formValues['actorIds'] = $this->actorId;
        $this->request->setMethod('POST')->setPost($formValues);
        $this->dispatch('admin/involvement/insert/origin/event/eventId/' . $this->eventId);
        $this->resetRequest()->resetResponse();
        $actor = Model_EntityMapper::getById($this->actorId);
        $involvements = Model_LinkMapper::getLinks($actor, ['P11', 'P14', 'P22', 'P23'], true);
        $this->dispatch('admin/involvement/update/origin/event/id/' . $involvements[0]->id);
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost($formValues);
        $this->dispatch('admin/involvement/update/origin/event/id/' . $involvements[0]->id);
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost($formValues);
        $this->dispatch('admin/involvement/update/origin/actor/id/' . $involvements[0]->id);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/actor/view/id/' . $this->actorId);
    }

    public function testDestruction() {
        $formValues = ['eventId' => $this->destructionId];
        $this->request->setMethod('POST')->setPost($formValues);
        $this->dispatch('admin/involvement/insert/origin/actor/actorId/' . $this->actorId);
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost($formValues);
        $actor = Model_EntityMapper::getById($this->actorId);
        $involvements = Model_LinkMapper::getLinks($actor, 'P11', true);
        $this->dispatch('admin/involvement/update/origin/actor/id/' . $involvements[0]->id);
    }

}
