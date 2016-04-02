<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_Class extends Model_AbstractObject {

    public $code;
    public $name;
    public $nameTranslated;
    public $commentTranslated;
    private $super = [];
    private $sub = [];

    public function getCodeName() {
        return $this->code . " " . $this->nameTranslated;
    }

    function getSuper() {
        return $this->super;
    }

    function addSuper(Model_Class $class) {
        $this->super[] = $class;
    }

    function getSub() {
        return $this->sub;
    }

    function addSub(Model_Class $class) {
        $this->sub[] = $class;
    }

    function getSubRecursive() {
        global $subsRecursive;
        $subsRecursive[] = $this->code;
        foreach ($this->sub as $sub) {
            $sub->getSubRecursive();
        }
        return $subsRecursive;
    }

}
