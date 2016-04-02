<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_DisplayErrors extends Zend_View_Helper_Abstract {

    public function displayErrors(Zend_Form $form) {
        $html = '';
        foreach ($form->getMessages() as $field => $errors) {
            $html .= '<div class="error">' . $form->getElement($field)->getLabel() . ':' .
                $this->view->formErrors($errors) . '</div>';
        }
        return $html;
    }

}
