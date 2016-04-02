<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_DisplayTreeOverlay extends Zend_View_Helper_Abstract {

    public function displayTreeOverlay($name, $treeData, $multi = false) {
        $html = '<div id="' . $name . 'Overlay" class="overlay"><div id="' . $name . 'Dialog" class="overlayContainer">
            <input class="treeFilter" id="' . $name . 'Search" placeholder="Filter"/><div id="' . $name . 'Tree"></div>
            </div></div>';
        $html .= '<script type="text/javascript">$(document).ready(function () {';

        if ($multi) {
            $html .= 'createTreeOverlay("' . $name . '", "' . $this->view->ucstring($name) . '", true);
                $("#' . $name . 'Tree").jstree({
                    "search": {"case_insensitive": true, "show_only_matches": true},
                    "plugins": ["search", "checkbox"],
                    "checkbox": { "three_state" : false },
                    "core": ' . $treeData . '});';
        } else {
            $html .= 'createTreeOverlay("' . $name . '", "' . $this->view->ucstring($name) . '");
                $("#' . $name . 'Tree").jstree({
                    "search": {"case_insensitive": true, "show_only_matches": true},
                    "plugins": ["search"],
                    "core": ' . $treeData . '});
                    $("#' . $name . 'Tree").on("select_node.jstree", function (e, data) {
                    selectFromTree("' . $name . '", data.node.id, data.node.text);});';
        }
        $html .= '$("#' . $name . 'Search").keyup(function () {
                $("#' . $name . 'Tree").jstree("search", $(this).val());});';
        $html .= '});</script>';
        return $html;
    }

}
