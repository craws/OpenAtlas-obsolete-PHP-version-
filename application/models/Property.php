<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_Property extends Model_AbstractObject {

    public $code;
    public $commentTranslated;
    public $domain;
    public $name;
    public $nameInverse;
    public $nameInverseTranslated;
    public $nameTranslated;
    public $range;
    public $super = [];
    public $sub = [];

    public function getCodeName() {
        return $this->code . " " . $this->nameTranslated;
    }

    public function getSuper() {
        return $this->super;
    }

    public function addSuper(Model_Property $super) {
        $this->super[] = $super;
    }

    public function getSub() {
        return $this->sub;
    }

    public function addSub(Model_Property $sub) {
        $this->sub[] = $sub;
    }

}
