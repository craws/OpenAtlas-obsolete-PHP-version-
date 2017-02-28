<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_Link extends Model_AbstractObject {

    public $description;
    public $domain;
    public $property;
    public $range;
    public $type;
    public $first; // for list views
    public $last; // for list views

}
