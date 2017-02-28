<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Actor extends Admin_Form_Base {

    public function init() {
        $this->setName('actorForm')->setMethod('post');
        $this->setAction($this->getView()->url());
        $this->addDates(['begin', 'begin2', 'end', 'end2']);
        $this->addElement('checkbox', 'birth', [
            'label' => $this->getView()->ucstring('birth'),
            'checkedValue' => 1,
            'uncheckedValue' => 0,
        ]);
        $this->addElement('checkbox', 'death', [
            'label' => $this->getView()->ucstring('death'),
            'checkedValue' => 1,
            'uncheckedValue' => 0,
        ]);
        $this->addElement('text', 'name', [
            'class' => 'required',
            'required' => true,
            'label' => $this->getView()->ucstring('name'),
        ]);
        $this->addElement('textarea', 'description', ['label' => $this->getView()->ucstring('description')]);
        $this->addElement('hidden', 'residenceId', ['decorators' => ['ViewHelper']]);
        $this->addElement('text', 'residenceButton', [
            'label' => $this->getView()->ucstring('residence'),
            'class' => 'tableSelect',
            'readonly' => true,
            'onfocus' => 'this.blur()',
            'placeholder' => $this->getView()->ucstring('select'),
            'attribs' => ['readonly' => 'true'],
        ]);
        $this->addElement('hidden', 'appearsFirstId', ['decorators' => ['ViewHelper']]);
        $this->addElement('text', 'appearsFirstButton', [
            'label' => $this->getView()->ucstring('appears_first'),
            'class' => 'tableSelect',
            'readonly' => true,
            'onfocus' => 'this.blur()',
            'placeholder' => $this->getView()->ucstring('select'),
            'attribs' => ['readonly' => 'true'],
        ]);
        $this->addElement('hidden', 'appearsLastId', ['decorators' => ['ViewHelper']]);
        $this->addElement('text', 'appearsLastButton', [
            'label' => $this->getView()->ucstring('appears_last'),
            'class' => 'tableSelect',
            'readonly' => true,
            'onfocus' => 'this.blur()',
            'placeholder' => $this->getView()->ucstring('select'),
            'attribs' => ['readonly' => 'true'],
        ]);
        $this->addElement('button', 'aliasAdd', ['label' => '+']);
        $this->addElement('hidden', 'aliasId', ['value' => 1]);
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
            'onclick' => "$('#continue').val(1);$('#actorForm').submit();return false;"
        ]);
        $this->setElementFilters(['StringTrim']);
    }

    public function prepareUpdate(Model_Entity $actor) {
        $aliasIndex = 0;
        $aliasElements = $actor->getLinkedEntities('P131');
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
        if ($actor->class->code != 'E21') {
            $this->removeElement('birth');
            $this->removeElement('death');
        }
    }

}
