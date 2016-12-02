<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_LanguageMapper extends Model_AbstractMapper {

    public static function getAll() {
        $languages = [];
        $sql = 'SELECT id, name, shortform, active FROM web.language ORDER BY name ASC;';
        foreach (parent::getAllRows($sql) as $row) {
            $languages[] = self::populate($row);
        }
        return $languages;
    }

    public static function getById($id) {
        $row = parent::getRowById('SELECT id, name, shortform, active FROM web.language WHERE id = :id', $id);
        return self::populate($row);
    }

    public static function getByShortform($shortform) {
        $sql = 'SELECT id, name, shortform, active FROM web.language WHERE shortform = :shortform';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':shortform', $shortform);
        $statement->execute();
        return self::populate($statement->fetch());
    }

    private static function populate(array $row) {
        $language = new Model_Language();
        $language->id = $row['id'];
        $language->name = $row['name'];
        $language->shortform = $row['shortform'];
        $language->active = $row['active'];
        return $language;
    }

}
