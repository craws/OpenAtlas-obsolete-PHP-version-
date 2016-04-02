<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_Ucstring extends Zend_View_Helper_Abstract {

    public function ucstring($string) {
        if (!$string) {
            return; // important because generic calls could be empty e.g. title in table header
        }
        $array = [];
        preg_match_all("~^(.)(.*)$~u", Zend_Registry::get('Zend_Translate')->translate($string), $array);
        return mb_strtoupper($array[1][0]) . $array[2][0];
    }

}
