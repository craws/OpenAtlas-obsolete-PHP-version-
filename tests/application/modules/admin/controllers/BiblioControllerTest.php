<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_BiblioControllerTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->login();
    }

    public function testInsertUpdate() {
        $this->dispatch('admin/biblio/insert/id/' . $this->sourceId);
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost([
            'referenceButton' => 'biblio',
            'referenceId' => $this->carrierId,
            'description' => 'desc'
        ]);
        $this->dispatch('admin/biblio/insert/id/' . $this->sourceId);
        $this->resetRequest()->resetResponse();
        $links = Model_LinkMapper::getLinks($this->carrierId, 'P128');
        $this->dispatch('admin/biblio/update/id/' . $links[0]->id . '/origin/reference');
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost(['description' => 'desc']);
        $this->dispatch('admin/biblio/update/id/' . $links[0]->id);
    }

}
