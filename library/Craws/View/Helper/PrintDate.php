<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_PrintDate extends Zend_View_Helper_Abstract {

    public function printDate(Zend_Date $zendDate, $format = Zend_Date::DATE_MEDIUM) {
        $zendDate->setLocale(Zend_Registry::get('Zend_Locale'));
        $date = $zendDate->toString($format);
        return $date;
    }

}
