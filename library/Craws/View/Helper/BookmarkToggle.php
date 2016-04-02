<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_BookmarkToggle extends Zend_View_Helper_Abstract {
    public function bookmarkToggle($entityId) {
        $label = $this->view->ucstring('bookmark');
        if (isset(Zend_Registry::get('user')->bookmarks[$entityId])) {
            $label = $this->view->ucstring('bookmark_remove');
        }
        $elementId = 'bookmark' . $entityId;
        $button = '<button id="' . $elementId . '" type="button" onclick="ajaxBookmark(' . $entityId . ');">' . $label . '</button>';
        return $button;
    }
}
