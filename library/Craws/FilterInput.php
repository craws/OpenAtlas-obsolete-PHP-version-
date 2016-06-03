<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

namespace Craws;

class FilterInput extends \Zend_Controller_Action_Helper_Abstract {

    public static function filter($string, $type = 'crm') {
        switch ($type) {
            case 'crm':
                $string = trim(preg_replace('/\s+/', ' ', $string)); // remove newlines
                return trim(strip_tags($string));
            case 'node':
                $string = preg_replace('/\s+/', '_', $string);  // replace whitespace with _
                return trim(mb_strtolower($string));
        // @codeCoverageIgnoreStart
        }
    }
    // @codeCoverageIgnoreEnd

}
