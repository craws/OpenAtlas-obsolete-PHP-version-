<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class FileController extends Zend_Controller_Action {

    public function viewAction() {
        $this->getHelper('layout')->disableLayout();
        $this->getHelper('viewRenderer')->setNoRender(true);
        switch ($this->_getParam('file')) {
            case 'schema':
                $image = file_get_contents(APPLICATION_PATH . '/../data/documentation/openatlas_schema_dpp.png');
                header('Content-type: image/jpg');
                echo $image;
                return;
            default:
                return;
        }
    }

}
