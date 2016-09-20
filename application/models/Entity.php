<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_Entity extends Model_AbstractObject {

    public $class;
    public $date;
    public $description;
    public $first; // for list views
    public $last; // for list views
    public $name;

    public function getLinks($codes, $inverse = false) {
        return Model_LinkMapper::getLinks($this, $codes, $inverse);
    }

    public function getLinkedEntities($code, $inverse = false) {
        return Model_LinkMapper::getLinkedEntities($this, $code, $inverse);
    }

    public function getLinkedEntity($code, $inverse = false) {
        return Model_LinkMapper::getLinkedEntity($this, $code, $inverse);
    }

    public function link($propertyCode, $range, $description = null) {
        return Model_LinkMapper::insert($propertyCode, $this, $range, $description);
    }

    /* getting part of a direced type e.g. Actor Actor relation: "Parent of (Child of)" */
    public function getNameDirected($inverse = false) {
        $array = explode('(', $this->name);
        // @codeCoverageIgnoreStart
        if ($inverse && isset($array[1])) {
            return trim(str_replace(['(', ')'], '', $array[1]));
        }
        // @codeCoverageIgnoreEnd
        return trim($array[0]);
    }

}
