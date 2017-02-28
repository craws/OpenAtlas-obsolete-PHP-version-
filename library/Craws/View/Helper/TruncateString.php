<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_TruncateString extends Zend_View_Helper_Abstract {

    public function truncateString($string, $length = 20) {
        if (mb_strlen($string, 'utf-8') >= $length + 2) {
            $title = str_replace('"', '', $string);
            $string = '<span title="' . $title . '">' . mb_substr($string, 0, $length, 'utf-8') . '..</span>';
        }
        return $string;
    }
}
