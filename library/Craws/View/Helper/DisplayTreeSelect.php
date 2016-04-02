<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_DisplayTreeSelect extends Zend_View_Helper_Abstract {

    public function displayTreeSelect($name, Zend_Form $form, $directed = false) {
        $elementName = $name . 'Button';
        $elementId = $name . 'Id';
        $displayName = ucfirst($name);
        if ($name == 'type') {
            $displayName = $this->view->ucstring($name);
        }
        if ($form->$elementName->isRequired()) {
            $displayName = $displayName . ' *';
        }
        $html = '<div class="tableRow">
            <div id="' . $elementName . '-label">
                <label class="optional" for="' . $elementName . '">' . $displayName . '</label>
                <span class="tooltip" title="' . $this->view->ucstring('tip_hierarchy') . '">i</span>
            </div>';
        $html .= '<div class="tableCell">';
        $html .= $form->$elementName->renderViewHelper();
        $html .= $form->$elementId->renderViewHelper();
        if ($directed) {
            $html .=  $form->inverse->renderViewHelper() . ' ' . $this->view->ucstring('inverse') . ' ';
        }
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
