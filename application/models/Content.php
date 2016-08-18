<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_Content extends Model_AbstractObject {

    public $texts;

    public function getText($name, $locale = null) {
        if (!$locale) {
            $locale = Zend_Registry::get('Zend_Locale');
        }
        $string = null;
        if (isset($this->texts[$locale][$name])) {
            $string = $this->texts[$locale][$name];
            // use default language if no translation available, too cumbersome to test
            // @codeCoverageIgnoreStart
            if (!$string && Zend_Controller_Front::getInstance()->getRequest()->getModuleName() == 'default') {
                $string = $this->texts[Zend_Registry::get('Default_Locale')][$name];
            }
            // @codeCoverageIgnoreEnd
        }
        return $string;
    }

}
