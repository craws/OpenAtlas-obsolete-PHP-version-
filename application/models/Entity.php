<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_Entity extends Model_AbstractObject {

    public $class;
    public $date;
    public $description;
    public $first; // for list views
    public $last; // for list views
    public $name;
    public $types = [];

    public function __toString() {
        return $this->name;
    }

    public function printTypes($rootName) {
        if (!isset($this->types[$rootName])) {
            return '';
        }
        $typeNames = [];
        foreach ($this->types[$rootName] as $type) {
            if ($type->rootId) {
                $typeNames[] = $type->name;
            }
        }
        return implode(',', $typeNames);
    }

    public function getTypesForView() {
        $printTypes = [];
        foreach ($this->types as $rootName => $types) {
            foreach ($types as $type) {
                if ($type->rootId && !in_array($type->name, ['Source Content'])) {
                    $printTypes[$rootName][] = $type->name;
                }
            }
        }
        ksort($printTypes);
        return $printTypes;
    }

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
