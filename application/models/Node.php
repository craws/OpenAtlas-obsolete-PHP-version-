<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_Node extends Model_Entity {

    public $directed = false;
    public $expandable = false;
    public $rootId = null;
    public $superId = null;
    public $subs = [];
    public $propertyToEntity;
    public $propertyToSuper;

    public function addSub(Model_Node $sub) {
        $this->subs[] = $sub;
    }

    public function getNameDirected($inverse = false) {
        $array = explode('(', $this->name);
        if ($inverse && isset($array[1])) {
            return trim(str_replace(['(', ')'], '', $array[1]));
        }
        return trim($array[0]);
    }

}
