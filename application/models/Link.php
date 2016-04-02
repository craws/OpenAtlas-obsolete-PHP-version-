<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_Link extends Model_AbstractObject {

    public $description;
    private $property;
    private $domain;
    private $range;

    public function getProperty() {
        return $this->property;
    }

    public function getDomain() {
        return $this->domain;
    }

    public function getRange() {
        return $this->range;
    }

    public function setProperty(Model_Property $property) {
        $this->property = $property;
    }

    public function setDomain(Model_Entity $domain) {
        $this->domain = $domain;
    }

    public function setRange(Model_Entity $range) {
        $this->range = $range;
    }

}
