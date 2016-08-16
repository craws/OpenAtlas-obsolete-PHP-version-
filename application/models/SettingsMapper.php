<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_SettingsMapper {

    public static function getSettings() {
        $sql = 'SELECT "name", "value", "group" FROM web.settings ORDER BY "group" ASC, "name" ASC;';
        $statement = Zend_DB_Table::getDefaultAdapter()->prepare($sql);
        $statement->execute();
        $rows = $statement->fetchall();
        if ($rows) {
            $settings = [];
            foreach ($rows as $row) {
                $settings[$row['group']][$row['name']] = $row['value'];
            }
            return $settings;
            // @codeCoverageIgnoreStart
        }
        echo "Something is rotten in the state of Denmark (no settings).";
        exit;
        // @codeCoverageIgnoreEnd
    }

    public static function getSetting($group, $name) {
        $settings = Zend_Registry::get('settings');
        if (isset($settings[$group][$name])) {
            return $settings[$group][$name];
        }
        // @codeCoverageIgnoreStart
        echo "Something is rotten in the state of Denmark (missing setting " . $group . "/" . $name . ").";
        exit;
        // @codeCoverageIgnoreEnd
    }

    public static function updateSettings($settings) {
        foreach ($settings as $group => $items) {
            foreach ($items as $name => $value) {
                $sql = 'UPDATE web.settings SET "value" = :value WHERE "name" = :name AND "group" = :group;';
                $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
                $statement->bindValue('name', $name);
                $statement->bindValue('value', $value);
                $statement->bindValue('group', $group);
                $statement->execute();
            }
        }
    }

}
