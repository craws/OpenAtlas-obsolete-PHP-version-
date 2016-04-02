<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_DisplayTableSelectMulti extends Zend_View_Helper_Abstract {

    public function displayTableSelectMulti($name, Zend_Form $form, $label, $required = false) {
        $requiredPostfix = '';
        if ($required) {
            $requiredPostfix = ' *';
        }
        $elementId = $name . 'Ids';
        $html = '<div class="tableRow">';
        $html .= '<div id="' . $name . 'Button-label">' . $this->view->ucstring($label) . $requiredPostfix . '</div>';
        $html .= '<div class="tableCell">';
        $html .= '<span id="' . $name . 'Button" class="button">' . $this->view->ucstring('select') . '</span>';
        $html .= '<br/><div id="' . $name . 'Selection" style="text-align:left;"></div>';
        $html .= $form->$elementId->renderViewHelper();
        $html .= '</div></div>';
        return $html;
    }

}
