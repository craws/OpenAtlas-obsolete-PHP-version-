<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_PlaceControllerTest extends ControllerTestCase {

    private $formValues = [
        'name' => 'Caras Galadhon',
        'description' => 'Andaran atish’an',
        'beginYear' => '-4',
        'beginMonth' => '1',
        'beginDay' => '1',
        'beginComment' => "Asha'belannar",
        'endYear' => '',
        'endMonth' => '',
        'endDay' => '',
        'endComment' => '',
        'alias' => ['alias0' => 'Newcastle'],
        'gisPoints' => '[{"type":"Feature","geometry":{"type":"Point","coordinates":[12.72356057154,47.92019822945]},
            "properties":{"name":"test", "description":"test","shapeType":"centerpoint"}}]'
    ];

    public function setUp() {
        parent::setUp();
        $this->login();
        $type = Model_NodeMapper::getByNodeCategoryName('site', 'ritual site');
        $this->formValues['siteId'] = $type->id;
        $administrative = Model_NodeMapper::getByNodeCategoryName('administrative unit', 'austria');
        $this->formValues['administrative_unitId'] = $administrative->id;
        $historical = Model_NodeMapper::getByNodeCategoryName('historical place', 'carantania');
        $this->formValues['historical_placeId'] = $historical->id;
    }

    public function testIndex() {
        $this->dispatch('admin/place');
    }

    public function testCrudPlace() {
        $this->dispatch('admin/place/insert');
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/place/insert/sourceId/' . $this->sourceId);
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/place/insert');
        $this->resetRequest()->resetResponse();
        $this->formValues['continue'] = 1;
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/place/insert');
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/place/insert/sourceId/' . $this->sourceId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/place/view/id/' . $this->objectId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/place/update/id/' . $this->objectId);
        $this->resetRequest()->resetResponse();
        $this->formValues['siteId'] = ''; // test empty root type
        $this->formValues['alias'] = '';
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/place/update/id/' . $this->objectId);
        $this->resetRequest()->resetResponse();
        $this->request->setMethod('POST')->setPost($this->formValues);
        $this->dispatch('admin/place/update/id/' . $this->objectId);
        $this->resetRequest()->resetResponse();
        $this->dispatch('admin/place/delete/id/' . $this->objectId);
    }

}
