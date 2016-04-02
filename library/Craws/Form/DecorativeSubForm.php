<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

namespace Craws\Form;

class DecorativeSubForm extends \Zend_Form_SubForm {

    public $decorators = [];

    public function addElement($element, $name = null, $options = null) {
        parent::addElement($element, $name, $options);
        if (null === $options || (is_array($options) && !isset($options['decorators']))) {
            if (!$element instanceof \Zend_Form_Element) {
                $element = $this->getElement($name);
            }
            foreach ($this->decorators as $class => $decorators) {
                if ($element instanceof $class) {
                    $element->setDecorators($decorators);
                    return $this;
                }
            }
        // @codeCoverageIgnoreStart
        }
        return $this;
        // @codeCoverageIgnoreEnd
    }

    public function loadDefaultDecorators() {
        if (isset($this->decorators['Zend_Form'])) {
            $this->setDecorators($this->decorators['Zend_Form']);
            // @codeCoverageIgnoreStart
        } else {
            parent::loadDefaultDecorators();
        }
        // @codeCoverageIgnoreEnd
    }

}
