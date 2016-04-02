<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_Entity extends Model_AbstractObject {

    public $date;
    public $description;
    public $first; // for list views
    public $last; // for list views
    public $name;
    private $class;

    public function getClass() {
        return $this->class;
    }

    public function setClass(Model_Class $class) {
        $this->class = $class;
    }

}
