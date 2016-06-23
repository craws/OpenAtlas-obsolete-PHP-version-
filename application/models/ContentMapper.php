<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_ContentMapper extends Model_AbstractMapper {

    public static function getById($id) {
        $row = parent::getRowById('SELECT id FROM web.content WHERE id = :id', $id);
        return self::populate($row);
    }

    public static function getAll() {
        $sql = 'SELECT id FROM web.content ORDER BY id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->execute();
        $rows = $statement->fetchAll();
        $contents = [];
        foreach ($rows as $row) {
            $contents[] = self::populate($row);
        }
        return $contents;
    }

    private static function populate(array $row) {
        $content = new Model_Content;
        $content->id = $row['id'];
        $content->texts = Model_TranslationMapper::get($row['id'], ['title', 'text']);
        return $content;
    }

    public static function update(Model_content $content) {
        Zend_Db_Table::getDefaultAdapter()->prepare('BEGIN;')->execute();
        Model_TranslationMapper::update($content->id, $content->texts);
        Zend_Db_Table::getDefaultAdapter()->prepare('COMMIT;')->execute();
    }

}
