<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_ReferenceControllerTest extends ControllerTestCase {

    public function setUp() {
        parent::setUp();
        $this->login();
    }

    public function testIndex() {
        $this->dispatch('admin/reference');
    }

    public function testView() {
        $this->dispatch('admin/reference/view/id/' . $this->biblioId);
    }

    public function testCrudReference() {
        $this->dispatch('admin/reference/insert/type/edition');
        $this->formValues = [
            'name' => 'Cryptonomicum',
            'typeId' => Model_NodeMapper::getByNodeCategoryName('Bibliography', 'Book')->id,
            'desc' => 'desc',
            'typeButton' => 'Placeholder',
        ];
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/reference/insert/type/edition');
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/reference/update/id/' . $this->biblioId);
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/reference/update/id/' . $this->biblioId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/reference/delete/id/' . $this->biblioId);
    }

    public function testCrudEdition() {
        $this->dispatch('admin/reference/insert/type/edition');
        $this->formValues = [
            'name' => 'Cryptonomicum Edition',
            'typeId' => Model_NodeMapper::getByNodeCategoryName('Edition', 'Charter Edition')->id,
            'typeButton' => 'Placeholder',
            'desc' => 'desc'
        ];
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/reference/insert/type/edition');
        $references = Model_EntityMapper::getByCodes('Bibliography');
        foreach ($references as $reference) {
            $type = Model_LinkMapper::getLinkedEntity($reference, 'P2');
            $typeRoot = Model_NodeMapper::getById($type->rootId);
        }
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/reference/update/id/' . $reference->id);
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/reference/update/id/' . $reference->id);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/reference/delete/id/' . $reference->id);
    }

}
