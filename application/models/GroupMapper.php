<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_GroupMapper extends Model_AbstractMapper {

    public static function getById($id) {
        $row = parent::getRowById('SELECT id, "name" FROM web.group WHERE id = :id', $id);
        return self::populate($row);
    }

    public static function getByName($name) {
        $sql = 'SELECT id, name FROM web.group WHERE name = :name;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':name', $name);
        $statement->execute();
        $row = $statement->fetch();
        $object = self::populate($row);
        return $object;
    }

    public static function getAll() {
        $objects = [];
        $sql = 'SELECT id, name FROM web.group ORDER BY name;';
        $rows = parent::getAllRows($sql);
        foreach ($rows as $row) {
            $objects[] = self::populate($row);
        }
        return $objects;
    }

    private static function populate(array $row) {
        $object = new Model_Group();
        $object->id = $row['id'];
        $object->name = $row['name'];
        return $object;
    }

}
