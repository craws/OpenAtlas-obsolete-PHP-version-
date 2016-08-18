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
            'information_carrierId' => $type->id,
            'desc' => 'desc',
            'objectId' => $this->objectId,
            'continue' => 1
        ];
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/carrier/insert');
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/carrier/update/id/' . $this->carrierId);
        $this->formValues['information_carrierId'] = '';
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/carrier/update/id/' . $this->carrierId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/carrier/update/id/' . $this->carrierId); // test with type which is not a root type
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/carrier/update/id/' . $this->carrierId); // test invalid form
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/carrier/delete/id/' . $this->carrierId);
    }

}
