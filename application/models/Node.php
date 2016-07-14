<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_Node extends Model_Entity {

    public $directional = false;
    public $extendable = false;
    public $forms = [];
    public $multiple = 0;
    public $propertyToEntity;
    public $propertyToSuper;
    public $rootId = null;
    public $superId = null;
    public $system = null;
    public $subs = [];

}
