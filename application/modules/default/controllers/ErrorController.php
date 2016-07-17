<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class ErrorController extends Zend_Controller_Action {

    public function missingAclAction() {
        $this->_helper->log('error', 'error', 'missing ACL');
        $this->_helper->message('error_site_not_found');
    }

    public function errorAction() {
        $errors = $this->_getParam('error_handler');
        $this->getResponse()->setHttpResponseCode(500);
        $exception = $errors->exception;
        if ($exception->getMessage() == "invalidId") {
            $this->_helper->message('error_non_existing_id');
        } else {
            // @codeCoverageIgnoreStart
            $string = (is_array($exception)) ? '<br>' . implode('<br>', $exception) : $exception;
            $this->_helper->log('critical', 'error', 'error_application' . ': ' . $string);
            $this->_helper->message('error_application');
            // @codeCoverageIgnoreEnd
        }
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        $this->view->request = $errors->request;
    }

}
