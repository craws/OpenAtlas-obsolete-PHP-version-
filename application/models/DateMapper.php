<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_DateMapper {

    public static function getDates(Model_Entity $entity) {
        $dates = [];
        foreach (['OA1', 'OA2', 'OA3', 'OA4', 'OA5', 'OA6'] as $code) {
            foreach (Model_LinkMapper::getLinkedEntities($entity, $code) as $date) {
                $type = Model_LinkMapper::getLinkedEntity($date, 'P2');
                $dates[$code][$type->name] = $date;
            }
        }
        return $dates;
    }

    public static function getLinkDates(Model_Link $link) {
        $dates = [];
        foreach (['OA5', 'OA6'] as $code) {
            foreach (Model_LinkPropertyMapper::getLinkedEntities($link, $code) as $date) {
                $dateType = Model_LinkMapper::getLinkedEntity($date, 'P2');
                $dates[$code][$dateType->name] = $date;
            }
        }
        return $dates;
    }

    public static function getLinkDateRange(Model_Link $link) {
        $sql = "
            SELECT
            (SELECT min(date_part('year', e.value_timestamp)) FROM model.entity e
            JOIN model.link_property lp ON e.id = lp.range_id
            JOIN model.link l ON lp.domain_id = l.id
            WHERE l.id = :link_id) AS first,
            max(date_part('year', e.value_timestamp)) AS last FROM model.entity e
            JOIN model.link_property lp ON e.id = lp.range_id
            JOIN model.link l ON lp.domain_id = l.id
            WHERE l.id = :link_id;";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':link_id', $link->id);
        $statement->execute();
        $row = $statement->fetch();
        return ['first' => $row['first'], 'last' => $row['last']];
    }

    public static function saveDates(Model_Entity $entity, Zend_Form $form) {
        $starttime = microtime(true);
        $time = microtime(true);
        Model_EntityMapper::deleteDates($entity);
        $timelog = sprintf('%04d',round((microtime(true) - $time)*1000)) . ' delete dates<br/>';
        $time = microtime(true);
        switch ($entity->class->name) {
            case 'Person':
                if ($form->getValue('birth')) {
                    self::insert($entity, $form, 'begin', 'OA3', 'Model_LinkMapper');
                } else {
                    self::insert($entity, $form, 'begin', 'OA1', 'Model_LinkMapper');
                }
                if ($form->getValue('death')) {
                    self::insert($entity, $form, 'end', 'OA4', 'Model_LinkMapper');
                } else {
                    self::insert($entity, $form, 'end', 'OA2', 'Model_LinkMapper');
                }
                break;
            case 'Activity':
            case 'Destruction':
            case 'Acquisition':
            case 'Production':
                self::insert($entity, $form, 'begin', 'OA5', 'Model_LinkMapper');
                self::insert($entity, $form, 'end', 'OA6', 'Model_LinkMapper');
                break;
            default:
                self::insert($entity, $form, 'begin', 'OA1', 'Model_LinkMapper');
                self::insert($entity, $form, 'end', 'OA2', 'Model_LinkMapper');
                break;
        }
        $timelog .= sprintf('%04d',round((microtime(true) - $time)*1000)) . ' insert dates<br/>';
        $timelog .= '--<br/></strong>' . sprintf('%04d',round((microtime(true) - $starttime)*1000)) . ' total<br/><br/>';
        echo($timelog);
        //die;
    }

    public static function saveLinkDates(Model_Link $link, Zend_Form $form) {
        self::insert($link, $form, 'begin', 'OA5', 'Model_LinkPropertyMapper');
        self::insert($link, $form, 'end', 'OA6', 'Model_LinkPropertyMapper');
    }

    private static function insert($entity, Zend_Form $form, $name, $code, $linkMapper) {
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
                $exactDate = Model_EntityMapper::insert('E61', '', $description, $date);
                Model_LinkMapper::insert('P2', $exactDate->id, $typeId['Exact date value']);
                $linkMapper::insert($code, $entity->id, $exactDate->id);
            } else if (strlen($date['month']) && !strlen($date['day'])) {
                $date1['year'] = $date['year'];
                $date1['month'] = $date['month'];
                $date1['day'] = 1;
                $fromDate = Model_EntityMapper::insert('E61', '', $description, $date1);
                Model_LinkMapper::insert('P2', $fromDate->id, $typeId['From date value']);
                $linkMapper::insert($code, $entity->id, $fromDate->id);
                $date2['year'] = $date['year'];
                $date2['month'] = $date['month'];
                $date2['day'] = $fromDate->date->get(Zend_Date::MONTH_DAYS);
                $toDate = Model_EntityMapper::insert('E61', '', $description, $date2);
                Model_LinkMapper::insert('P2', $toDate->id, $typeId['To date value']);
                $linkMapper::insert($code, $entity->id, $toDate->id);
            } else {
                $date1['year'] = $date['year'];
                $date1['month'] = 1;
                $date1['day'] = 1;
                $fromDate = Model_EntityMapper::insert('E61', '', $description, $date1);
                Model_LinkMapper::insert('P2', $fromDate->id, $typeId['From date value']);
                $linkMapper::insert($code, $entity->id, $fromDate->id);
                $date2['year'] = $date['year'];
                $date2['month'] = 12;
                $date2['day'] = 31;
                $toDate = Model_EntityMapper::insert('E61', '', $description, $date2);
                Model_LinkMapper::insert('P2', $toDate->id, $typeId['To date value']);
                $linkMapper::insert($code, $entity->id, $toDate->id);
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
            $fromDate = Model_EntityMapper::insert('E61', '', $description, $date1);
            Model_LinkMapper::insert('P2', $fromDate->id, $typeId['From date value']);
            $linkMapper::insert($code, $entity->id, $fromDate->id);
            $date2['year'] = $date['year2'];
            $date2['month'] = $date1['month'];
            if (strlen($date['month2'])) {
                $date2['month'] = $date['month2'];
            }
            $date2['day'] = $date1['day'];
            if (strlen($date['day2'])) {
                $date2['day'] = $date['day2'];
            }
            $toDate = Model_EntityMapper::insert('E61', '', $description, $date2);
            Model_LinkMapper::insert('P2', $toDate->id, $typeId['To date value']);
            $linkMapper::insert($code, $entity->id, $toDate->id);
        }
    }

}
