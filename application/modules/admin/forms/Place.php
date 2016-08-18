<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Place extends Admin_Form_Base {

    public function init() {
        $this->setName('placeForm')->setMethod('post');
        $this->setAction($this->getView()->url());
        $this->addElement('text', 'name', [
            'class' => 'required',
            'required' => true,
            'label' => $this->getView()->ucstring('name'),
        ]);
        $this->addElement('button', 'aliasAdd', ['label' => '+']);
        $this->addElement('hidden', 'aliasId', ['value' => 1]);
        $this->addDates(['begin', 'begin2', 'end', 'end2']);
        $this->addElement('text', 'easting', [
            'label' => $this->getView()->ucstring('easting'),
            'validators' => array(array('Float', true, array('locale' => 'en'))),
            'placeholder' => '16.371568'
        ]);
        $this->addElement('text', 'northing', [
            'label' => $this->getView()->ucstring('northing'),
            'validators' => array(array('Float', true, array('locale' => 'en'))),
            'placeholder' => '48.208121'
        ]);
        $this->addElement('textarea', 'description', ['label' => $this->getView()->ucstring('description')]);
        $submitLabel = 'save';
        if (Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'insert') {
            $submitLabel = 'insert';
        }
        $this->addElement('hidden', 'modified');
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring($submitLabel), 'type' => 'submit']);
        $this->addElement('hidden', 'continue', ['decorators' => ['ViewHelper'], 'value' => 0]);
        $this->addElement('button', 'continueButton', [
            'label' => $this->getView()->ucstring('insert_and_continue'),
            'type' => 'submit',
            'onclick' => "$('#continue').val(1);$('#placeForm').submit();return false;"
        ]);
        $this->setElementFilters(['StringTrim']);
    }

    public function prepareUpdate(Model_Entity $object) {
        $aliasIndex = 0;
        $aliasElements = Model_LinkMapper::getLinkedEntities($object, 'P1');
        if ($aliasElements) {
            foreach ($aliasElements as $alias) {
                $element = $this->createElement('text', 'alias' . $aliasIndex, ['belongsTo' => 'alias']);
                $element->setValue($alias->name);
                $this->addElement($element);
                $aliasIndex++;
            }
        } else {
            $element = $this->createElement('text', 'alias0', ['belongsTo' => 'alias']);
            $this->addElement($element);
            $aliasIndex++;
        }
        $this->populate(['aliasId' => $aliasIndex]);
    }

}
