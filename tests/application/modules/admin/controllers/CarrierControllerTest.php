<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_CarrierControllerTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->login();
    }

    public function testView() {
        $this->dispatch('admin/carrier/view/id/' . $this->carrierId);
    }

    public function testCrudCarrier() {
        $this->dispatch('admin/carrier/insert');
        $type = Model_NodeMapper::getByNodeCategoryName('Information Carrier', 'Original Document');
        $this->formValues = [
            'name' => 'Cryptonomicum',
            'typeId' => $type->id,
            'typeButton' => $type->name,
            'desc' => 'desc',
            'objectId' => $this->objectId
        ];
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/carrier/insert');
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/carrier/update/id/' . $this->carrierId);
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/carrier/update/id/' . $this->carrierId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/carrier/update/id/' . $this->carrierId);
        $this->dispatch('admin/carrier/delete/id/' . $this->carrierId);
    }

}
