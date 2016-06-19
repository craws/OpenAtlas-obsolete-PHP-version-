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
                $find = array(' ', '&', '\r\n', '\n', '+', ','); // adding _ for spaces and union characters
                $string = str_replace($find, '_', strtolower(trim($string)));
                $find1 = array('/[^._a-z0-9\-<>]/', '/[\-]+/', '/<[^>]*>/'); // delete and replace rest of special chars
                return preg_replace($find1, array('', '_', ''), $string);
            // @codeCoverageIgnoreStart
        }
    }

    // @codeCoverageIgnoreEnd
}
