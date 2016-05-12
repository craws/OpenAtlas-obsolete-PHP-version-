<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_MemberControllerTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->login();
    }

    public function testCrud() {
        $formValues = [
            'relatedActorIds' => $this->actorId,
            'typeId' => Model_NodeMapper::getByNodeCategoryName('type', 'Actor Function', 'King')->id,
            'relatedActorButton' => 'Placeholder',
            'beginYear' => '23'
        ];
        $this->dispatch('admin/member/insert/id/' . $this->groupId);
        $this->request->setMethod('POST')->setPost($formValues);
        $this->dispatch('admin/member/insert/id/' . $this->groupId);
        $links = Model_LinkMapper::getLinks($this->groupId, 'P107');
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/member/update/id/' . $links[0]->id . '/originActorId/' . $this->actorId);
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost($formValues);
        $this->dispatch('admin/member/update/id/' . $links[0]->id . '/originActorId/' . $this->actorId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/member/member/id/' . $this->actorId);
        $this->resetRequest()->resetResponse();
        $formValues['relatedActorIds'] = $this->groupId;
        $this->request->setMethod('POST')->setPost($formValues);
        $this->dispatch('admin/member/member/id/' . $this->actorId);
        $this->resetRequest()->resetResponse();
    }

}
