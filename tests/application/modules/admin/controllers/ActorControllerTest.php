<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_ActorControllerTest extends ControllerTestCase {

    private $formValues = [
        'name' => 'Hector',
        'description' => 'Hector',
        'beginYear' => '23',
        'beginMonth' => '12',
        'beginDay' => '23',
        'beginComment' => 'comment',
        'endYear' => '24',
        'endMonth' => '12',
        'endDay' => '24',
        'birth' => 1,
        'death' => 1,
        'alias' => ['alias0' => 'Ramirez'],
    ];

    public function setUp() {
        parent::setUp();
        $this->login();
        $this->formValues['residenceId'] = $this->objectId;
        $this->formValues['appearsFirstId'] = $this->objectId;
        $this->formValues['appearsLastId'] = $this->objectId;
        $this->formValues['genderId'] = Model_NodeMapper::getByNodeCategoryName('Gender', 'Female')->id;
    }

    public function testIndex() {
        $this->dispatch('admin/actor');
    }

    public function testCrudActor() {
        $this->dispatch('admin/actor/insert'); // test errror if code is missing
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/actor/insert/code/E21/');
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/actor/insert/code/E21/sourceId/' . $this->sourceId);
        $this->resetRequest()->resetResponse();
        $actors = Model_EntityMapper::getByCodes('Person');
        $actorId = $actors[0]->id;
        $this->dispatch('admin/actor/view/id/' . $actorId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/actor/view/id/' . $this->actorId); // for biblio link
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/actor/view/id/' . $this->groupId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/actor/update/id/' . $actorId);
        $this->resetRequest()->resetResponse();
        $this->formValues['birth'] = 0;
        $this->formValues['death'] = 0;
        $this->formValues['genderId'] = ''; // test empty sytem type
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/actor/update/id/' . $actorId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/actor/delete/id/' . $actorId);
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/actor/insert/code/E40/eventId/' . $this->eventId);
        $this->resetRequest()->resetResponse();
        $legalBodies = Model_EntityMapper::getByCodes('Group');
        $this->dispatch('admin/actor/update/id/' . $legalBodies[0]->id);
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/actor/update/id/' . $legalBodies[0]->id);
        $this->resetRequest()->resetResponse();
        $this->formValues['name'] = '';
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/actor/update/id/' . $legalBodies[0]->id); // test invalid form
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/actor/view/id/' . $legalBodies[0]->id);
    }

    public function testRelation() {
        $this->dispatch('admin/actor/insert-relation/id/' . $this->actorId);
        $relation = Model_NodeMapper::getByNodeCategoryName('Actor Actor Relation', 'Kindredship');
        $this->request->setMethod('POST')->setPost([
            'typeId' => $relation->id,
            'typeButton' => $relation->name,
            'name' => 'relation',
            'relatedActorId' => $this->actorId,
            'description' => 'description',
        ]);
        $this->dispatch('admin/actor/insert-relation/id/' . $this->actorId);
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost([
            'typeId' => $relation->id,
            'typeButton' => $relation->name,
            'inverse' => '1',
            'name' => 'relation',
            'relatedActorId' => $this->actorId,
            'description' => 'inverse',
        ]);
        $this->dispatch('admin/actor/insert-relation/id/' . $this->actorId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/actor/delete-relation/id' . $this->actorId . '/relationId/1');
    }

}
