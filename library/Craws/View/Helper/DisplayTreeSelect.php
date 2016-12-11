<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_DisplayTreeSelect extends Zend_View_Helper_Abstract {

    public function displayTreeSelect(array $hierarchies, Zend_Form $form, $forType = False) {
        usort($hierarchies, function($a, $b) {
            return strcmp($a->name, $b->name);
        });
        $html = '';
        foreach ($hierarchies as $hierarchy) {
            $elementName = $hierarchy->nameClean . 'Button';
            $elementId = $hierarchy->nameClean . 'Id';
            $displayName = ucfirst($hierarchy->name);
            $class = (in_array($hierarchy->name, ['administrative', 'historical'])) ? ' placeSwitch display-none' : '';
            $tip = $this->view->ucstring('tip_hierarchy');
            $tip .= ($hierarchy->description) ? '&#013;' . str_replace('"', '', $hierarchy->description) : '';
            $html .= '<div class="tableRow' . $class . '"><div id="' . $elementName . '-label">';
            if ($forType) {
                $html .= '<label class="optional" for="' . $elementName . '">Super</label>';
            } else  {
                $html .= '<label class="optional" for="' . $elementName . '">' . $displayName . '</label>';
                $html .= ' <span class="tooltip" title="' . $tip . '">i</span>';
            }
            $html .= '</div>';
            $html .= '<div class="tableCell">';
            $html .= $form->$elementId->renderViewHelper();
            if ($hierarchy->multiple) {
                $html .= '<span id="' . $hierarchy->nameClean . 'Button" class="button">' . $this->view->ucstring('change') . '</span><br/>';
                $selectionElement = $hierarchy->nameClean . 'Selection';
                $html .= '<div style="text-align:left;" id="' . $hierarchy->nameClean . 'Selection">' . $form->$selectionElement->renderViewHelper() . '</div>';
            } else {
                $html .= $form->$elementName->renderViewHelper();
                $html .= ($form->inverse) ? $form->inverse->renderViewHelper() . ' ' . $this->view->ucstring('inverse') . ' ' : '';
                $display = (!$form->$elementName->getValue()) ? 'style="display: none;"' : '';
                $html .= ' <a id="' . $hierarchy->nameClean . 'Clear" class="button" ' . $display . ' onclick="clearSelect(\'' . $hierarchy->nameClean .
                    '\');">' . $this->view->ucstring('clear') . '</a>';
            }
            $html .= '</div></div>';
        }
        return $html;
    }

}
