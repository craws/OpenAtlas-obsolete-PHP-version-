<?php

class ErrorController extends Zend_Controller_Action {

    // @codeCoverageIgnoreStart
    public function forbiddenAction() {
        $this->_helper->log('error', 'error', 'forbidden');
        $this->_helper->message('error_forbidden');
    }

    // @codeCoverageIgnoreEnd

    public function missingAclAction() {
        $this->_helper->log('error', 'error', 'missing ACL');
        $this->_helper->message('error_site_not_found');
    }

    public function errorAction() {
        $errors = $this->_getParam('error_handler');
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:
            default:
                $this->getResponse()->setHttpResponseCode(500);
                $exception = $errors->exception;
                // @codeCoverageIgnoreStart
                if (is_array($exception)) {
                    $exception = '<br>' . implode('<br>', $exception);
                }
                // @codeCoverageIgnoreEnd
                if ($exception->getMessage() == "invalidId") {
                    $this->_helper->message('error_non_existing_id');
                    // @codeCoverageIgnoreStart
                } else {
                    $this->_helper->log('critical', 'error', 'error_application' . ': ' . $exception);
                    $this->_helper->message('error_application');
                    break;
                }
                // @codeCoverageIgnoreStart
        }
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }
        $this->view->request = $errors->request;
    }

}
