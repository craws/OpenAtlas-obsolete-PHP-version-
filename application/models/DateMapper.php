<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_DateMapper {

    public static function getDates(Model_Entity $entity) {
        $dates = [];
        foreach (['OA1', 'OA2', 'OA3', 'OA4', 'OA5', 'OA6'] as $code) {
            foreach ($entity->getLinkedEntities($code) as $date) {
                $type = $date->types['Date value type'][0];
                $dates[$code][$type->name] = $date;
            }
        }
        return $dates;
    }

    public static function getLinkDates(Model_Link $link) {
        $dates = [];
        foreach (['OA5', 'OA6'] as $code) {
            foreach (Model_LinkPropertyMapper::getLinkedEntities($link, $code) as $date) {
                $type = $date->types['Date value type'][0];
                $dates[$code][$type->name] = $date;
            }
        }
        return $dates;
    }

    public static function saveDates(Model_Entity $entity, Zend_Form $form) {
        Model_EntityMapper::deleteDates($entity);
        switch ($entity->class->name) {
            case 'Person':
                if ($form->getValue('birth')) {
                    self::insert($entity->id, $form, 'begin', 'OA3', 'Model_LinkMapper');
                } else {
                    self::insert($entity->id, $form, 'begin', 'OA1', 'Model_LinkMapper');
                }
                if ($form->getValue('death')) {
                    self::insert($entity->id, $form, 'end', 'OA4', 'Model_LinkMapper');
                } else {
                    self::insert($entity->id, $form, 'end', 'OA2', 'Model_LinkMapper');
                }
                break;
            case 'Activity':
            case 'Destruction':
            case 'Acquisition':
            case 'Production':
                self::insert($entity->id, $form, 'begin', 'OA5', 'Model_LinkMapper');
                self::insert($entity->id, $form, 'end', 'OA6', 'Model_LinkMapper');
                break;
            default:
                self::insert($entity->id, $form, 'begin', 'OA1', 'Model_LinkMapper');
                self::insert($entity->id, $form, 'end', 'OA2', 'Model_LinkMapper');
                break;
        }
    }

    public static function saveLinkDates($linkId, Zend_Form $form) {
        self::insert($linkId, $form, 'begin', 'OA5', 'Model_LinkPropertyMapper');
        self::insert($linkId, $form, 'end', 'OA6', 'Model_LinkPropertyMapper');
    }

    private static function insert($id, Zend_Form $form, $name, $code, $linkMapper) {
        if (!$form->getValue($name . 'Year')) {
            return false;
        }
        $typeId = [];
        foreach(Model_NodeMapper::getHierarchyByName('Date value type')->subs as $type) {
            $typeId[$type->name] = $type->id;
        }
        $description = trim($form->getValue($name . 'Comment'));
        $date['year'] = $form->getValue($name . 'Year');
        $date['month'] = $form->getValue($name . 'Month');
        $date['day'] = $form->getValue($name . 'Day');
        $date['year2'] = $form->getValue($name . '2Year');
        $date['month2'] = $form->getValue($name . '2Month');
        $date['day2'] = $form->getValue($name . '2Day');
        if (!strlen($date['year2'])) {
            if (strlen($date['month']) && strlen($date['day'])) {
                $exactDateId = Model_EntityMapper::insert('E61', '', $description, $date);
                Model_LinkMapper::insert('P2', $exactDateId, $typeId['Exact date value']);
                $linkMapper::insert($code, $id, $exactDateId);
            } else if (strlen($date['month']) && !strlen($date['day'])) {
                $date1['year'] = $date['year'];
                $date1['month'] = $date['month'];
                $date1['day'] = 1;
                $fromDateId = Model_EntityMapper::insert('E61', '', $description, $date1);
                $fromDate = Model_EntityMapper::getById($fromDateId);
                Model_LinkMapper::insert('P2', $fromDateId, $typeId['From date value']);
                $linkMapper::insert($code, $id, $fromDateId);
                $date2['year'] = $date['year'];
                $date2['month'] = $date['month'];
                $date2['day'] = $fromDate->date->get(Zend_Date::MONTH_DAYS);
                $toDateId = Model_EntityMapper::insert('E61', '', $description, $date2);
                Model_LinkMapper::insert('P2', $toDateId, $typeId['To date value']);
                $linkMapper::insert($code, $id, $toDateId);
            } else {
                $date1['year'] = $date['year'];
                $date1['month'] = 1;
                $date1['day'] = 1;
                $fromDateId = Model_EntityMapper::insert('E61', '', $description, $date1);
                Model_LinkMapper::insert('P2', $fromDateId, $typeId['From date value']);
                $linkMapper::insert($code, $id, $fromDateId);
                $date2['year'] = $date['year'];
                $date2['month'] = 12;
                $date2['day'] = 31;
                $toDateId = Model_EntityMapper::insert('E61', '', $description, $date2);
                Model_LinkMapper::insert('P2', $toDateId, $typeId['To date value']);
                $linkMapper::insert($code, $id, $toDateId);
            }
        } else {
            $date1['year'] = $date['year'];
            $date1['month'] = 1;
            if (strlen($date['month'])) {
                $date1['month'] = $date['month'];
            }
            $date1['day'] = 1;
            if (strlen($date['day'])) {
                $date1['day'] = $date['day'];
            }
            $fromDateId = Model_EntityMapper::insert('E61', '', $description, $date1);
            Model_LinkMapper::insert('P2', $fromDateId, $typeId['From date value']);
            $linkMapper::insert($code, $id, $fromDateId);
            $date2['year'] = $date['year2'];
            $date2['month'] = $date1['month'];
            if (strlen($date['month2'])) {
                $date2['month'] = $date['month2'];
            }
            $date2['day'] = $date1['day'];
            if (strlen($date['day2'])) {
                $date2['day'] = $date['day2'];
            }
            $toDateId = Model_EntityMapper::insert('E61', '', $description, $date2);
            Model_LinkMapper::insert('P2', $toDateId, $typeId['To date value']);
            $linkMapper::insert($code, $id, $toDateId);
        }
    }

}
