<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_AbstractMapper {

    public static function getRowById($sql, $id, $failureException = true) {
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue('id', (int) $id);
        $statement->execute();
        if ($statement->rowCount() != 1 && $failureException) {
            Model_LogMapper::log('debug', 'non_existing_id', $id . " - " . $sql);
            throw new \Zend_Application_Bootstrap_Exception("invalidId");
        }
        return $statement->fetch();
    }

    public static function getAllRows($sql) {
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->execute();
        return $statement->fetchAll();
    }

    public static function deleteAbstract($table, $id) {
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare('DELETE FROM ' . $table . ' WHERE id = :id;');
        $statement->bindValue('id', (int) $id);
        $statement->execute();
    }

    public static function toZendDate($date) {
        if (!$date) {
            return null;
        }
        // @codeCoverageIgnoreStart
        if (is_a($date, "Zend_Date")) {
            return $date;
        }
        // @codeCoverageIgnoreEnd
        if (strpos($date, 'BC') !== FALSE) { // looks like zend date ignores bc in a postgresql timestamp string
            return $zendDate = new Zend_Date('-' . $date, Zend_Date::ISO_8601, 'en');
        }
        return new Zend_Date($date, Zend_Date::ISO_8601, 'en');
    }

    public static function toDbDate($date) {
        if (!is_a($date, 'Zend_Date')) {
            return null;
        }
        $year = $date->get(Zend_Date::YEAR);
        // @codeCoverageIgnoreStart
        if ($year < -4713) {
            return null; // postgresql timestamps not possible before -4713
        }
        // @codeCoverageIgnoreEnd
        $date->setLocale('en');
        if ($year[0] != '-') {
            $string = $date->get(Zend_Date::MONTH_NAME_SHORT) . ', ' . $date->get(Zend_Date::DAY) . ' ' .
                sprintf('%04d', $date->get(Zend_Date::YEAR)) . ' ' . $date->get(Zend_Date::HOUR) . ':' .
                $date->get(Zend_Date::MINUTE) . ':' . $date->get(Zend_Date::SECOND);
            return $string;
        }
        $date->setYear(abs($year));
        return (String) $date . " BC";
    }

}
