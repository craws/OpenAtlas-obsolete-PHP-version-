<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_Log extends Model_AbstractObject {

    public $priority;
    public $type;
    public $message;
    public $userId;
    public $ip;
    public $agent;
    public $params;

}
