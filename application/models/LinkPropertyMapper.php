<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_LinkPropertyMapper extends Model_AbstractMapper {

    private static $sqlSelect = 'SELECT l.id, l.property_id, l.domain_id, l.range_id, l.created, l.modified ';

    public static function getLinkedEntity(Model_Link $link, $code) {
        $linkedEntity = self::getLink($link, $code);
        if (!$linkedEntity) {
            return false;
        }
        $entity = $linkedEntity->range;
        return $entity;
    }

    public static function getLinkedEntities(Model_Link $link, $code) {
        $entities = [];
        foreach (self::getLinks($link, $code) as $link) {
            $entities[] = $link->range;
        }
        return $entities;
    }

    public static function getLink(Model_Link $link, $code) {
        $links = self::getLinks($link, $code);
        switch (count($links)) {
            case 0:
                return false;
            case 1:
                return $links[0];
            // @codeCoverageIgnoreStart
        }
        $error = 'Found ' . count($links) . ' ' . $code . ' property links for link(' . $link->id . ') instead one.';
        Model_LogMapper::log('error', 'model', $error);
    }

    // @codeCoverageIgnoreEnd

    public static function getLinks(Model_Link $link, $code) {
        $codes = (is_array($code)) ? $code : [$code];
        $sql = self::$sqlSelect . ", e.name
            FROM model.link_property l
            JOIN model.entity e ON l.range_id = e.id
            JOIN model.property p ON l.property_id = p.id AND p.code IN ('" . implode("','", $codes) . "')
            WHERE l.domain_id = :domain_id ORDER BY e.name;";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':domain_id', $link->id);
        $statement->execute();
        $objects = [];
        foreach ($statement->fetchAll() as $row) {
            $objects[] = self::populate($row);
        }
        return $objects;
    }

    public static function getByEntity(Model_Entity $entity) {
        $objects = [];
        $sql = 'SELECT l.id, l.property_id, l.domain_id, l.range_id, l.created, l.modified, e.name FROM
            model.link_property l JOIN model.entity e ON l.range_id = e.id WHERE l.range_id = :range_id ORDER BY e.name;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':range_id', $entity->id);
        $statement->execute();
        // @codeCoverageIgnoreStart
        // Ignore coverage because cumbersome to test (an involvement with a type would be needed in data_test.sql)
        foreach ($statement->fetchAll() as $row) {
            $objects[] = self::populate($row);
        }
        // @codeCoverageIgnoreEnd
        return $objects;
    }

    private static function populate(array $row) {
        $link = new Model_LinkProperty();
        $link->id = $row['id'];
        $link->property = Model_PropertyMapper::getById($row['property_id']);
        $link->domain = Model_LinkMapper::getById($row['domain_id']);
        if (in_array($row['range_id'], Zend_Registry::get('nodesIds'))) {
            $link->range = Model_NodeMapper::getById($row['range_id']);
        } else {
            $link->range = Model_EntityMapper::getById($row['range_id']);
        }
        return $link;
    }

    public static function insert($code, $domainId, $rangeId) {
        $sql = 'INSERT INTO model.link_property (property_id, domain_id, range_id)
            VALUES (:property_id, :domain_id, :range_id);';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':property_id', Model_PropertyMapper::getByCode($code)->id);
        $statement->bindValue(':domain_id', $domainId);
        $statement->bindValue(':range_id', $rangeId);
        $statement->execute();
    }

    public static function insertTypeLinks($linkId, Zend_Form $form, array $hierarchies) {
        foreach ($hierarchies as $hierarchy) {
            $idField = $hierarchy->nameClean . 'Id';
            if ($form->getValue($idField)) {
                foreach (explode(",", $form->getValue($idField)) as $id) {
                    self::insert('P2', $linkId, $id);
                }
            } else if ($hierarchy->system) {
                self::insert('P2', $linkId, $hierarchy->id);
            }
        }
    }

}
