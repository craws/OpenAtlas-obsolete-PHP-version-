<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_LinkPropertyMapper extends Model_AbstractMapper {

    private static $sqlSelect = 'SELECT l.id, l.property_id, l.domain_id, l.range_id, l.created, l.modified ';

    public static function getById($id) {
        $row = parent::getRowById(self::$sqlSelect . ' FROM model.link_property l WHERE l.id = :id;', $id);
        return self::populate($row);
    }

    public static function getLinkedEntity(Model_Link $link, $code) {
        $linkedEntity = self::getLink($link, $code);
        if (!$linkedEntity) {
            return false;
        }
        $entity = $linkedEntity->getRange();
        if ($code == 'P2') {
            $entity = Model_NodeMapper::getById($entity->id);
        }
        return $entity;
    }

    public static function getLinkedEntities(Model_Link $link, $code) {
        $entities = [];
        foreach (self::getLinks($link, $code) as $link) {
            $entities[] = $link->getRange();
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
        $sql = self::$sqlSelect . ', e.name FROM model.link_property l JOIN model.entity e ON l.range_id = e.id
            WHERE l.property_id = :property_id AND l.domain_id = :domain_id ORDER BY e.name;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':property_id', Model_PropertyMapper::getByCode($code)->id);
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
        foreach ($statement->fetchAll() as $row) {
            $objects[] = self::populate($row);
        }
        return $objects;
    }

    private static function populate(array $row) {
        $link = new Model_LinkProperty();
        $link->id = $row['id'];
        $link->setProperty(Model_PropertyMapper::getById($row['property_id']));
        $link->setDomain(Model_LinkMapper::getById($row['domain_id']));
        $link->setRange(Model_EntityMapper::getById($row['range_id']));
        return $link;
    }

    public static function insert($code, Model_Link $domain, Model_Entity $range) {
        $sql = 'INSERT INTO model.link_property (property_id, domain_id, range_id)
      VALUES (:property_id, :domain_id, :range_id) RETURNING id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':property_id', Model_PropertyMapper::getByCode($code)->id);
        $statement->bindValue(':domain_id', $domain->id);
        $statement->bindValue(':range_id', $range->id);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $link = Model_LinkPropertyMapper::getById($result['id']);
        Model_LogMapper::log('info', 'insert', 'insert LinkProperty (' . $link->id . ')');
        Model_UserLogMapper::insert('LinkProperty', $link->id, 'insert');
        return $link;
    }

    public static function delete(Model_Link $link) {
        parent::deleteAbstract('model.link', $link->id);
    }

}
