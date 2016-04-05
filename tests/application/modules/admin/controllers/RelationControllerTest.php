<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_RelationControllerTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->login();
    }

    public function testCrud() {
        $socialId = Model_NodeMapper::getByNodeCategoryName('type', 'Actor Actor Relation', 'Social')->id;
        $this->dispatch('admin/relation/insert/id/' . $this->actorId);
        $this->request->setMethod('POST')->setPost([
            'relatedActorIds' => $this->actorId,
            'typeId' => $socialId,
            'typeButton' => 'Placeholder',
            'beginYear' => '23'
        ]);
        $this->dispatch('admin/relation/insert/id/' . $this->actorId);
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost([
            'relatedActorIds' => $this->actorId,
            'typeId' => $socialId,
            'typeButton' => 'Placeholder',
            'inverse' => 1,
            'beginYear' => '1',
            'beginMonth' => '2'
        ]);
        $this->dispatch('admin/relation/insert/id/' . $this->actorId);
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost([
            'relatedActorIds' => $this->actorId,
            'typeId' => $socialId,
            'typeButton' => 'Placeholder',
            'inverse' => 1,
            'beginYear' => '1',
            'beginMonth' => '2',
            'beginDay' => '1',
            'begin2Year' => '1',
            'begin2Month' => '1',
            'begin2Day' => '1',
        ]);
        $this->dispatch('admin/relation/insert/id/' . $this->actorId);
        $this->resetRequest()->resetResponse();
        $links = Model_LinkMapper::getLinks($this->actorId, 'OA7');
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/relation/update/id/' . $links[0]->id . '/originActorId/' . $this->actorId);
        $this->request->setMethod('POST')->setPost(['typeButton' => 'Placeholder', 'typeId' => $socialId, 'inverse' => 0]);
        $this->dispatch('admin/relation/update/id/' . $links[0]->id . '/originActorId/' . $this->actorId);
        $this->resetRequest()->resetResponse();
        $links2 = Model_LinkMapper::getLinks($this->actorId, 'OA7');
        $this->dispatch('admin/relation/update/id/' . $links2[0]->id . '/originActorId/' . $this->actorId);
        $this->request->setMethod('POST')->setPost(['typeButton' => 'Placeholder', 'typeId' => $socialId, 'inverse' => 1]);
        $this->dispatch('admin/relation/update/id/' . $links2[0]->id . '/originActorId/' . $this->actorId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/actor/view/id/' . $this->actorId);
    }

}
