<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_LanguageMenu extends Zend_View_Helper_Abstract {

    public function languageMenu() {
        $items = [];
        foreach (Model_LanguageMapper::getAll() as $language) {
            if ($language->shortform != Zend_Registry::get('Zend_Locale')) {
                $items[] = '<a href="?lang=' . $language->shortform . '">' . mb_strtoupper($language->shortform) . '</a>';
            } else {
                $items[] = '<span>' . mb_strtoupper($language->shortform) . '</span>';
            }
        }
        return implode(' ', $items);
    }

}
