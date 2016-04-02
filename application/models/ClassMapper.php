<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_ClassMapper extends Model_AbstractMapper {

    public static function getAll() {
        $sql = "
            SELECT c.id, c.code, c.name, c.created, c.modified,
              COALESCE (
                (SELECT text FROM crm.i18n WHERE table_name LIKE 'class' AND table_field LIKE 'name' AND
                  table_id = c.id AND language_code LIKE :language_code),
                (SELECT text FROM crm.i18n WHERE table_name LIKE 'class' AND table_field LIKE 'name' AND
                  table_id = c.id AND language_code LIKE :language_default_code)
              ) as name_i18n,
              COALESCE (
                (SELECT text FROM crm.i18n WHERE table_name LIKE 'class' AND table_field LIKE 'comment' AND
                  table_id = c.id AND language_code LIKE :language_code),
                (SELECT text FROM crm.i18n WHERE table_name LIKE 'class' AND table_field LIKE 'comment' AND
                  table_id = c.id AND language_code LIKE :language_default_code)
              ) as comment_i18n
            FROM crm.class c";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':language_code', Zend_Registry::get('Zend_Locale'));
        $statement->bindValue(':language_default_code', Zend_Registry::get('Default_Locale'));
        $statement->execute();
        $rows = $statement->fetchAll();
        $classes = [];
        foreach ($rows as $row) {
            $classes[$row['id']] = self::populate($row);
        }
        $statement2 = Zend_Db_Table::getDefaultAdapter()->prepare('SELECT super_id, sub_id FROM crm.class_inheritance;');
        $statement2->execute();
        foreach ($statement2->fetchAll() as $row) {
            $classes[$row['super_id']]->addSub($classes[$row['sub_id']]);
            $classes[$row['sub_id']]->addSuper($classes[$row['super_id']]);
        }
        return $classes;
    }

    public static function getByCode($code) {
        foreach (Zend_Registry::get('classes') as $class) {
            if ($class->code == $code) {
                return $class;
            }
        }
        return false;
    }

    private static function populate(array $row) {
        $class = new Model_Class();
        $class->id = $row['id'];
        $class->code = $row['code'];
        $class->name = $row['name'];
        $class->created = parent::toZendDate($row['created']);
        $class->modified = parent::toZendDate($row['modified']);
        $class->nameTranslated = $row['name_i18n'];
        $class->commentTranslated = $row['comment_i18n'];
        return $class;
    }

}
