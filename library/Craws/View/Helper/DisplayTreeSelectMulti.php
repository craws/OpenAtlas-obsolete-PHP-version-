<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_DisplayTreeSelectMulti extends Zend_View_Helper_Abstract {

    public function displayTreeSelectMulti($name, Zend_Form $form, $selection = null) {
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
        $displayName = ucfirst($name);
        if ($name == 'type') {
            $displayName = $this->view->ucstring($name);
        }
        $html = '<div class="tableRow">';
        $html = '<div class="tableRow">
            <div id="' . $this->view->ucstring($name) . '-label">
                <label class="optional">' . $displayName . '</label>
                <span class="tooltip" title="' . $this->view->ucstring('tip_hierarchy') . '">i</span>
            </div>';
        $html .= '<div class="tableCell">';
        $html .= '<span id="' . $name . 'Button" class="button">' . $this->view->ucstring('change') . '</span><br/>';
        $elementId = $name . 'Id';
        $form->$elementId->setValue($htmlIds);
        $html .= $form->$elementId->renderViewHelper();
        $html .= '<div id="' . $name . 'Selection" style="text-align:left;">' . $htmlNames . '</div>';
        $html .= '</div></div>';
        return $html;
    }

}
