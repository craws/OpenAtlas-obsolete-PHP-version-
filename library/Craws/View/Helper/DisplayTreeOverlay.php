<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_DisplayTreeOverlay extends Zend_View_Helper_Abstract {

    public function displayTreeOverlay($hierarchy, $treeData) {
        if (!is_a($hierarchy, 'Model_Node')) {
            $hierarchy = Model_NodeMapper::getHierarchyByName($hierarchy);
        }
        $html = '<div id="' . $hierarchy->nameClean . 'Overlay" class="overlay">
                    <div id="' . $hierarchy->nameClean . 'Dialog" class="overlayContainer">
                        <input class="treeFilter" id="' . $hierarchy->nameClean . 'Search" placeholder="Filter"/>
                        <div id="' . $hierarchy->nameClean . 'Tree"></div>
                    </div>
                </div>';
        $html .= '<script type="text/javascript">$(document).ready(function () {';
        if ($hierarchy->multiple) {
            $html .= 'createTreeOverlay("' . $hierarchy->nameClean . '", "' . $hierarchy->name . '", true);
                $("#' . $hierarchy->nameClean . 'Tree").jstree({
                    "search": {"case_insensitive": true, "show_only_matches": true},
                    "plugins": ["search", "checkbox"],
                    "checkbox": { "three_state" : false },
                    "core": ' . $treeData . '});';
        } else {
            $html .= 'createTreeOverlay("' . $hierarchy->nameClean . '", "' . $hierarchy->name . '");
                $("#' . $hierarchy->nameClean . 'Tree").jstree({
                    "search": {"case_insensitive": true, "show_only_matches": true},
                    "plugins": ["search"],
                    "core": ' . $treeData . '});
                    $("#' . $hierarchy->nameClean . 'Tree").on("select_node.jstree", function (e, data) {
                    selectFromTree("' . $hierarchy->nameClean . '", data.node.id, data.node.text);});';
        }
        $html .= '$("#' . $hierarchy->nameClean . 'Search").keyup(function () {
                $("#' . $hierarchy->nameClean . 'Tree").jstree("search", $(this).val());});';
        $html .= '});</script>';
        return $html;
    }

}
