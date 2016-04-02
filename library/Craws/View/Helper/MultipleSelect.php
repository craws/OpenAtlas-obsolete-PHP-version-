<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_MultipleSelect extends Zend_View_Helper_Abstract {

    /** build html for an array of select arrays with variable amounts (e.g. alias) */
    public function multipleSelect(Zend_Form $form, $name) {
        $html = '';
            $fieldId = $name . 'ElementId';
            $html = '<div class="tableRow">' . $form->$fieldId->renderViewHelper();
            $html .= '<div><label>' . $this->view->ucstring($name) . '</label></div>';
            $html .= '<div class="tableCell">';
            for ($i = 0; $i < $form->$fieldId->getValue(); $i++) {
                $html .= ($i == 0) ? ' ' : '<br/>';
                $fieldName = $name . $i;
                $html .= $form->$fieldName->renderViewHelper();
            }
            $fieldAdd = $name . 'ElementAdd';
            $html .= $form->$fieldAdd->renderViewHelper() . '</div></div>';
        return $html;
    }

}
