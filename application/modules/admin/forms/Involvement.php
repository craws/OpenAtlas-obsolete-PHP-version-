<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Involvement extends Craws\Form\Table {

    public function init() {
        $this->setName('involvementForm')->setMethod('post');
        $this->setAction($this->getView()->url());
        Admin_Form_Abstract::addDates($this, ['begin', 'begin2', 'end', 'end2']);
        $this->addElement('hidden', 'involvementId', ['decorators' => ['ViewHelper']]);
        $this->addElement('text', 'involvementButton', [
            'label' => 'Involvement',
            'class' => 'tableSelect',
            'readonly' => true,
            'onfocus' => 'this.blur()',
            'placeholder' => $this->getView()->ucstring('select'),
            'attribs' => ['readonly' => 'true'],
        ]);
        $this->addElement('hidden', 'actorIds', [
            'decorators' => ['ViewHelper'],
            'required' => true,
            'class' => 'required'
        ]);
        $this->addElement('hidden', 'eventId', ['decorators' => ['ViewHelper'], 'required' => true]);
        $this->addElement('text', 'eventButton', [
            'label' => $this->getView()->ucstring('event'),
            'class' => 'required tableSelect',
            'required' => true,
            'readonly' => true,
            'onfocus' => 'this.blur()',
            'placeholder' => $this->getView()->ucstring('select'),
            'attribs' => ['readonly' => 'true'],
        ]);
        $this->addElement('textarea', 'description', [
            'label' => $this->getView()->ucstring('description'),
            'style' => 'width:25em;height:5em;'
        ]);
        $submitLabel = 'save';
        if (Zend_Controller_Front::getInstance()->getRequest()->getActionName() == 'insert') {
            $submitLabel = 'insert';
        }
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring($submitLabel), 'type' => 'submit']);
        $this->setElementFilters(['StringTrim']);
    }

    public function addActivity($event) {
        if ($event && $event->getClass()->code == 'E6') {
            return;
        }
        $activity = $this->createElement('select', 'activity', ['required' => true, 'class' => 'required']);
        $activity->setLabel($this->getView()->ucstring('activity'));
        $activity->addMultiOptions(['' => html_entity_decode('&nbsp;')]);
        $options = ['P11', 'P14'];
        if (!$event || $event->getClass()->code == 'E8') {
            $options[] = 'P22';
            $options[] = 'P23';
        }
        foreach ($options as $code) {
            $property = Model_PropertyMapper::getByCode($code);
            $activity->addMultiOptions([$property->id => $property->nameInverseTranslated]);
        }
        $this->addElement($activity);
    }

}
