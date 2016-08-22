<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

abstract class Model_AbstractObject {

    public $id;
    public $created;
    public $modified;

    public function update() {
        $mapper = get_called_class() . 'Mapper';
        $mapper::update($this);
    }

    public function insert() {
        $mapper = get_called_class() . 'Mapper';
        $id = $mapper::insert($this);
        return $id;
    }

    public function delete() {
        $mapper = get_called_class() . 'Mapper';
        $mapper::delete($this);
    }

}
