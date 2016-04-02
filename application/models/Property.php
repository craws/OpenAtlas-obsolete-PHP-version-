<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_Property extends Model_AbstractObject {

    public $code;
    public $name;
    public $nameTranslated;
    public $nameInverse;
    public $nameInverseTranslated;
    public $commentTranslated;
    private $domain;
    private $range;
    private $super = [];
    private $sub = [];

    public function getDomain() {
        return $this->domain;
    }

    public function setDomain(Model_Class $domain) {
        $this->domain = $domain;
    }

    public function getRange() {
        return $this->range;
    }

    public function setRange(Model_Class $range) {
        $this->range = $range;
    }

    public function getCodeName() {
        return $this->code . " " . $this->nameTranslated;
    }

    function getSuper() {
        return $this->super;
    }

    function addSuper(Model_Property $super) {
        $this->super[] = $super;
    }

    function getSub() {
        return $this->sub;
    }

    function addSub(Model_Property $sub) {
        $this->sub[] = $sub;
    }

}
