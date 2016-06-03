<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_DisplayTreeSelect extends Zend_View_Helper_Abstract {

    public function displayTreeSelect($hierarchy, Zend_Form $form, $selection = null) {
        if (!is_a($hierarchy, 'Model_Node')) {
            $hierarchy = Model_NodeMapper::getRootType($hierarchy);
        }
        $elementName = $hierarchy->nameClean . 'Button';
        $elementId = $hierarchy->nameClean . 'Id';
        $displayName = ucfirst($hierarchy->name);
        $class = (in_array($hierarchy->name, ['administrative', 'historical'])) ? ' placeSwitch display-none' : '';
        $html = '<div class="tableRow' . $class . '">
            <div id="' . $elementName . '-label">
                <label class="optional" for="' . $elementName . '">' . $displayName . '</label>
                <span class="tooltip" title="' . $this->view->ucstring('tip_hierarchy') . '">i</span>
            </div>';
        $html .= '<div class="tableCell">';
        if ($hierarchy->multiple) {
            $ids = [];
            $htmlIds = '';
            $htmlNames = '';
            if ($selection) {
                foreach ($selection as $item) {
                    $ids[] = $item->id;
                    $htmlNames .= $item->name . '<br/>';
                }
                $htmlIds = implode(',', $ids);
            }
            $html .= '<span id="' . $hierarchy->nameClean . 'Button" class="button">' . $this->view->ucstring('change') . '</span><br/>';
            $form->$elementId->setValue($htmlIds);
            $html .= $form->$elementId->renderViewHelper();
            $html .= '<div id="' . $hierarchy->nameClean . 'Selection" style="text-align:left;">' . $htmlNames . '</div>';
        } else {
            $html .= $form->$elementName->renderViewHelper();
            $html .= $form->$elementId->renderViewHelper();
            if ($hierarchy->directional) {
                $html .= $form->inverse->renderViewHelper() . ' ' . $this->view->ucstring('inverse') . ' ';
            }
            $display = (!$form->$elementName->getValue()) ? 'style="display: none;"' : '';
            $html .= '<a id="' . $hierarchy->nameClean . 'Clear" class="button" ' . $display . ' onclick="clearSelect(\'' . $hierarchy->nameClean .
                '\');">' . $this->view->ucstring('clear') . '</a>';
        }
        return $html . '</div></div>';
    }

}
