<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

abstract class Model_AbstractObject {

    public $id;
    public $created;
    public $modified;

    public function update() {
        $mapper = get_called_class() . 'Mapper';
        $mapper::update($this);
        Model_LogMapper::log(
          'info', 'update', 'update ' . str_replace('Model_', '', get_called_class()) . ' (' . $this->id . ')'
        );
    }

    public function insert() {
        $mapper = get_called_class() . 'Mapper';
        $id = $mapper::insert($this);
        Model_LogMapper::log(
          'info', 'insert', 'insert ' . str_replace('Model_', '', get_called_class()) . ' (' . $id . ')'
        );
        return $id;
    }

    public function delete() {
        $mapper = get_called_class() . 'Mapper';
        $mapper::delete($this);
        Model_LogMapper::log(
          'info', 'delete', 'delete ' . str_replace('Model_', '', get_called_class()) . ' (' . $this->id . ')'
        );
    }

}
