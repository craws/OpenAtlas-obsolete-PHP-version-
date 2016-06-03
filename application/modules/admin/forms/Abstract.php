<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Abstract extends Craws\Form\Table {

    public function addDates(Zend_Form $form, $names) {
        foreach ($names as $name) {
            $label = (strpos($name, '2') == TRUE) ? "" : $form->getView()->ucstring($name);
            $year = $form->createElement('text', $name . 'Year', [
                'label' => $label,
                'class' => 'year',
                'style' => 'text-align:right;width:4em;',
                'placeholder' => $form->getView()->translate('yyyy')
            ]);
            $year->addValidator(new Zend_Validate_Int(), true);
            $year->addValidator(new Zend_Validate_GreaterThan(['min' => -4714]));
            $form->addElement($year);
            $month = $form->createElement('text', $name . 'Month', [
                'class' => 'month',
                'style' => 'text-align:right;width:2em;',
                'maxlength' => '2',
                'placeholder' => $form->getView()->translate('mm')
            ]);
            $month->addValidator(new Zend_Validate_Digits(), true);
            $month->addValidator(new Zend_Validate_LessThan(13));
            $form->addElement($month);
            $day = $form->createElement('text', $name . 'Day', [
                'class' => 'day',
                'style' => 'text-align:right;width:2em;',
                'maxlength' => '2',
                'placeholder' => $form->getView()->translate('dd'),
            ]);
            $day->addValidator(new Zend_Validate_Digits(), true);
            $day->addValidator(new Zend_Validate_LessThan(32));
            $form->addElement($day);
            if (strpos($name, '2') == FALSE) {
                $form->addElement('text', $name . 'Comment', ['placeholder' => $form->getView()->translate('comment')]);
            }
        }
    }

    public static function populateDates(Zend_Form $form, $element, array $dateFields) {
        if (is_a($element, 'Model_Entity')) {
            $dates = Model_DateMapper::getDates($element);
        } else {
            $dates = Model_DateMapper::getLinkDates($element);
        }
        foreach ($dateFields as $key => $field) {
            if (isset($dates[$key]['Exact date value'])) {
                $date = $dates[$key]['Exact date value']->date;
                $form->populate([
                    $field . 'Year' => $date->get(Zend_Date::YEAR),
                    $field . 'Month' => $date->get(Zend_Date::MONTH_SHORT),
                    $field . 'Day' => $date->get(Zend_Date::DAY_SHORT),
                    $field . 'Comment' => $dates[$key]['Exact date value']->description,
                ]);
            } else if (isset($dates[$key]['From date value']) && isset($dates[$key]['To date value'])) {
                $date1 = $dates[$key]['From date value']->date;
                $date2 = $dates[$key]['To date value']->date;
                $form->populate([
                    $field . 'Year' => $date1->get(Zend_Date::YEAR),
                    $field . 'Month' => $date1->get(Zend_Date::MONTH_SHORT),
                    $field . 'Day' => $date1->get(Zend_Date::DAY_SHORT),
                    $field . 'Comment' => $dates[$key]['From date value']->description,
                    $field . '2Year' => $date2->get(Zend_Date::YEAR),
                    $field . '2Month' => $date2->get(Zend_Date::MONTH_SHORT),
                    $field . '2Day' => $date2->get(Zend_Date::DAY_SHORT),
                ]);
            }
        }
        if (isset($dates['OA3'])) {
            $form->populate(['birth' => 1]);
        }
        if (isset($dates['OA4'])) {
            $form->populate(['death' => 1]);
        }
    }

    public static function preValidation(Zend_Form $form, array $data) {
        foreach ($data['alias'] as $key => $name) {
            $form->addElement('text', $key, ['belongsTo' => 'alias']);
        }
    }

    public function addHierarchies(Zend_Form $form, $hierarchies) {
        foreach ($hierarchies as $hierarchy) {
            $form->addElement('hidden', $hierarchy->nameClean . 'Id', ['decorators' => ['ViewHelper']]);
            $form->addElement('text', $hierarchy->nameClean . 'Button', [
                'label' => $hierarchy->name,
                'class' => 'tableSelect',
                'readonly' => true,
                'onfocus' => 'this.blur()',
                'placeholder' => $this->getView()->ucstring('select'),
                'attribs' => ['readonly' => 'true'],
            ]);
        }

    }

}
