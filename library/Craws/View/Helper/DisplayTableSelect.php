<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_DisplayTableSelect extends Zend_View_Helper_Abstract {

    public function displayTableSelect($name, Zend_Form $form) {
        $elementName = $name . 'Button';
        $elementId = $name . 'Id';
        $html = '<div class="tableRow">';
        $html .= $form->$elementName->renderLabel();
        $html .= '<div class="tableCell">';
        $html .= $form->$elementName->renderViewHelper();
        $html .= $form->$elementId->renderViewHelper();
        if (!$form->$elementName->isRequired()) {
            $display = '';
            if (!$form->$elementName->getValue()) {
                $display = 'style="display: none;"';
            }
            $html .= '<a id="' . $name . 'Clear" class="button" ' . $display . ' onclick="clearSelect(\'' . $name .
                '\');">' . $this->view->ucstring('clear') . '</a>';
        }
        $html .= '</div></div>';
        return $html;
    }

}
