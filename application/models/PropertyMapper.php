<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_PropertyMapper extends Model_AbstractMapper {

    public static function getAll() {
        $sql = "SELECT p.id, p.code, p.domain_class_id, p.range_class_id, p.name, p.name_inverse, p.created, p.modified,
                COALESCE (
                  (SELECT text FROM model.i18n WHERE table_name = 'property' AND table_field = 'name' AND
                    table_id = p.id AND language_code = :language_code),
                  (SELECT text FROM model.i18n WHERE table_name = 'property' AND table_field = 'name' AND
                    table_id = p.id AND language_code = :language_default_code)
                ) as name_i18n,
                COALESCE (
                  (SELECT text FROM model.i18n WHERE table_name = 'property' AND table_field = 'name_inverse' AND
                    table_id = p.id AND language_code = :language_code),
                  (SELECT text FROM model.i18n WHERE table_name = 'property' AND table_field = 'name_inverse' AND
                    table_id = p.id AND language_code = :language_default_code)
                ) as name_inverse_i18n,
                COALESCE (
                  (SELECT text FROM model.i18n WHERE table_name = 'property' AND table_field = 'comment' AND
                    table_id = p.id AND language_code = :language_code),
                  (SELECT text FROM model.i18n WHERE table_name = 'property' AND table_field = 'comment' AND
                    table_id = p.id AND language_code = :language_default_code)
                ) as comment_i18n
                FROM model.property p";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':language_code', Zend_Registry::get('Zend_Locale'));
        $statement->bindValue(':language_default_code', Zend_Registry::get('Default_Locale'));
        $statement->execute();
        $classes = Zend_Registry::get('classes');
        $properties = [];
        foreach ($statement->fetchAll() as $row) {
            $properties[$row['id']] = self::populate($row);
            $properties[$row['id']]->domain = $classes[$row['domain_class_id']];
            $properties[$row['id']]->range = $classes[$row['range_class_id']];
        }
        $sqlInheritance = 'SELECT super_id, sub_id FROM model.property_inheritance;';
        $statementInheritance = Zend_Db_Table::getDefaultAdapter()->prepare($sqlInheritance);
        $statementInheritance->execute();
        foreach ($statementInheritance->fetchAll() as $row) {
            $properties[$row['super_id']]->addSub($properties[$row['sub_id']]);
            $properties[$row['sub_id']]->addSuper($properties[$row['super_id']]);
        }
        return $properties;
    }

    public static function getByCode($code) {
        foreach (Zend_Registry::get('properties') as $property) {
            if ($property->code == $code) {
                return $property;
            }
        }
    }

    public static function getById($id) {
        $properties = Zend_Registry::get('properties');
        return $properties[$id];
    }

    private static function populate(array $row) {
        $property = new Model_Property();
        $property->id = $row['id'];
        $property->code = $row['code'];
        $property->name = $row['name'];
        $property->nameInverse = $row['name_inverse'];
        $property->created = parent::toZendDate($row['created']);
        $property->modified = parent::toZendDate($row['modified']);
        $property->nameTranslated = $row['name_i18n'];
        $property->nameInverseTranslated = $row['name_inverse_i18n'];
        $property->commentTranslated = $row['comment_i18n'];
        return $property;
    }

}
