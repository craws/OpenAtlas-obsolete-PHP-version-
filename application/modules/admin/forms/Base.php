<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_Base extends Craws\Form\Table {

    public function addDates($names) {
        foreach ($names as $name) {
            $label = (strpos($name, '2') == TRUE) ? "" : $this->getView()->ucstring($name);
            $year = $this->createElement('text', $name . 'Year', [
                'label' => $label,
                'class' => 'year',
                'style' => 'text-align:right;width:4em;',
                'placeholder' => $this->getView()->translate('yyyy')
            ]);
            $year->addValidator(new Zend_Validate_Int(), true);
            $year->addValidator(new Zend_Validate_GreaterThan(['min' => -4714]));
            $this->addElement($year);
            $month = $this->createElement('text', $name . 'Month', [
                'class' => 'month',
                'style' => 'text-align:right;width:2em;',
                'maxlength' => '2',
                'placeholder' => $this->getView()->translate('mm')
            ]);
            $month->addValidator(new Zend_Validate_Digits(), true);
            $month->addValidator(new Zend_Validate_LessThan(13));
            $this->addElement($month);
            $day = $this->createElement('text', $name . 'Day', [
                'class' => 'day',
                'style' => 'text-align:right;width:2em;',
                'maxlength' => '2',
                'placeholder' => $this->getView()->translate('dd'),
            ]);
            $day->addValidator(new Zend_Validate_Digits(), true);
            $day->addValidator(new Zend_Validate_LessThan(32));
            $this->addElement($day);
            if (strpos($name, '2') == FALSE) {
                $this->addElement('text', $name . 'Comment', ['placeholder' => $this->getView()->translate('comment')]);
            }
        }
    }

    public function populateDates($element, array $dateFields) {
        if (is_a($element, 'Model_Entity')) {
            $dates = Model_DateMapper::getDates($element);
        } else {
            $dates = Model_DateMapper::getLinkDates($element);
        }
        foreach ($dateFields as $key => $field) {
            if (isset($dates[$key]['Exact date value'])) {
                $date = $dates[$key]['Exact date value']->date;
                $this->populate([
                    $field . 'Year' => $date->get(Zend_Date::YEAR),
                    $field . 'Month' => $date->get(Zend_Date::MONTH_SHORT),
                    $field . 'Day' => $date->get(Zend_Date::DAY_SHORT),
                    $field . 'Comment' => $dates[$key]['Exact date value']->description,
                ]);
            } else if (isset($dates[$key]['From date value']) && isset($dates[$key]['To date value'])) {
                $date1 = $dates[$key]['From date value']->date;
                $date2 = $dates[$key]['To date value']->date;
                $this->populate([
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
            $this->populate(['birth' => 1]);
        }
        if (isset($dates['OA4'])) {
            $this->populate(['death' => 1]);
        }
    }

    public function preValidation(array $data) {
        foreach ($data['alias'] as $key => $name) {
            $name = $name; // ignore this, its just to get rid of netbeans unused variable warning
            $this->addElement('text', $key, ['belongsTo' => 'alias']);
        }
    }

    public function addHierarchies($formName, $entity = null) {
        $forms = Zend_Registry::get('forms');
        $hierarchies = [];
        foreach ($forms[$formName]['hierarchyIds'] as $hierarchyId) {
            $hierarchy = Model_NodeMapper::getById($hierarchyId);
            $hierarchies[] = $hierarchy;
            $this->addElement('hidden', $hierarchy->nameClean . 'Id', ['decorators' => ['ViewHelper']]);
            $this->addElement('text', $hierarchy->nameClean . 'Button', [
                'label' => $hierarchy->name,
                'class' => 'tableSelect',
                'readonly' => true,
                'onfocus' => 'this.blur()',
                'placeholder' => $this->getView()->ucstring('select'),
                'attribs' => ['readonly' => 'true'],
            ]);
            $treeVariable = $hierarchy->nameClean . 'TreeData';
            $nodes = ($entity) ? Model_NodeMapper::getNodesByEntity($hierarchy->name, $entity) : [];
            $nodeIds = [];
            $nodeNames = [];
            foreach ($nodes as $node) {
                $nodeIds[] = $node->id;
                $nodeNames[] = $node->name;
            }
            $this->populate([$hierarchy->nameClean . 'Id' => implode(',', $nodeIds)]);
            if ($node->multiple) {
                /* To do */
                //$this->getView()->nameClean . $selectionElement, implode('</br>', $nodeNames);
            } else {
                $this->populate([$hierarchy->nameClean . 'Button' => implode('</br>', $nodeNames)]);
            }
            $this->getView()->$treeVariable = Model_NodeMapper::getTreeData($hierarchy->name, $nodes);
        }
        $this->getView()->hierarchies = $hierarchies;

    }

}
