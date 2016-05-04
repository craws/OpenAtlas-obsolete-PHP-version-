<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_DisplayAliasForm extends Zend_View_Helper_Abstract {

    public function displayAliasForm(Zend_Form $form) {
        $html = '<div class="tableRow">' . $form->aliasId->renderViewHelper();
        $html .= '<div><label>' . $this->view->ucstring('alias') . '</label></div><div class="tableCell">';
        for ($i = 0; $i < $form->aliasId->getValue(); $i++) {
            $html .= ($i == 0) ? '' : '<br/>';
            $fieldName = 'alias' . $i;
            $html .= $form->$fieldName->renderViewHelper();
        }
        $html .= $form->aliasAdd->renderViewHelper();
        $html .= '</div></div>';
        return $html;
    }

}
