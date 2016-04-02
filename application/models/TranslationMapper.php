<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_TranslationMapper {

    public static function insert($itemId, $texts) {
        foreach (Model_LanguageMapper::getAll() as $language) {
            foreach ($texts[$language->shortform] as $name => $text) {
                $sql = 'INSERT INTO web.i18n (field, text, item_id, language_id) ' .
                    'VALUES (:field, :text, :item_id, :language_id);';
                $statement = Zend_Db_table::getDefaultAdapter()->prepare($sql);
                $statement->bindValue('field', $name);
                $statement->bindValue('text', trim($text));
                $statement->bindValue('item_id', $itemId);
                $statement->bindValue('language_id', $language->id);
                $statement->execute();
            }
        }
    }

    public static function get($itemId, $fields) {
        $sql = "SELECT i.id, i.text, i.field, l.shortform FROM web.i18n i LEFT JOIN web.language l " .
          "ON i.language_id = l.id WHERE field = ANY (:fields) AND i.item_id = :item_id;";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue('fields', '{' . implode(",", $fields) . '}');
        $statement->bindValue('item_id', $itemId);
        $statement->execute();
        $rows = $statement->fetchAll();
        $texts = [];
        foreach ($rows as $row) {
            $texts[$row['shortform']][$row['field']] = $row['text'];
        }
        return $texts;
    }

    public static function update($itemId, $texts) {
        self::delete($itemId, $texts);
        self::insert($itemId, $texts);
    }

    public static function delete($itemId, $texts) {
        foreach (array_keys(reset($texts)) as $name) {
            $sql = 'DELETE FROM web.i18n WHERE field LIKE :field AND item_id = :item_id;';
            $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
            $statement->bindValue('field', $name);
            $statement->bindValue('item_id', $itemId);
            $statement->execute();
        }
    }

}
