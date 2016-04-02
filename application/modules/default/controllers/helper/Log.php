<?php

class Controller_Helper_Log extends Zend_Controller_Action_Helper_Abstract {

    public function direct($priority, $type, $message = "") {
        Model_LogMapper::log($priority, $type, $message);
    }

}
