<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_Entity extends Model_AbstractObject {

    public $class;
    public $date;
    public $description;
    public $first; // for list views
    public $last; // for list views
    public $name;

    /* for getting part of a direced type e.g. Actor Actor relation: "Parent of (Child of)" */
    public function getNameDirected($inverse = false) {
        $array = explode('(', $this->name);
        // @codeCoverageIgnoreStart
        // Ignore coverage because cumbersome to test
        if ($inverse && isset($array[1])) {
            return trim(str_replace(['(', ')'], '', $array[1]));
        }
        // @codeCoverageIgnoreEnd
        return trim($array[0]);
    }

}
