<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_Gis extends Model_AbstractObject {

    public $northing;
    public $easting;
    private $entity;

    public function getEntity() {
        return $this->entity;
    }

    public function setEntity(Model_Entity $entity) {
        $this->entity = $entity;
    }

}
