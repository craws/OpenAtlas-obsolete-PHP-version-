<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_LinkMapper extends Model_AbstractMapper {

    private static $sqlSelect = 'SELECT l.id, l.property_id, l.domain_id, l.range_id, l.description, l.created, l.modified ';

    public static function getById($id) {
        $row = parent::getRowById(self::$sqlSelect . ' FROM model.link l WHERE l.id = :id;', $id);
        return self::populate($row);
    }

    public static function getLinkedEntity($entity, $code, $inverse = false) {
        $linkedEntity = self::getLink($entity, $code, $inverse);
        if (!$linkedEntity) {
            return false;
        }
        if ($inverse) {
            return $linkedEntity->domain;
        }
        return $linkedEntity->range;
    }

    public static function getLinkedEntities($entity, $code, $inverse = false) {
        $entities = [];
        foreach (self::getLinks($entity, $code, $inverse) as $link) {
            if ($inverse) {
                $entities[$link->domain->id] = $link->domain;
            } else {
                $entities[$link->range->id] = $link->range;
            }
        }
        return $entities;
    }

    public static function getLink($entity, $code, $inverse = false) {
        $links = self::getLinks($entity, $code, $inverse);
        switch (count($links)) {
            case 0:
                return false;
            case 1:
                return $links[0];
                // @codeCoverageIgnoreStart
                // Ignore coverage because cumbersome to test this failure
        }
        $error = 'Found ' . count($links) . ' ' . $code . ' links for (' . $entity->name . ')' . ' instead one.';
        if (is_a($entity, 'Model_Entity')) {
            $error = 'Found ' . count($links) . ' ' . $code . ' links for (' . $entity->id . ')' . ' instead one.';
        }
        Model_LogMapper::log('error', 'model', $error);
    }
    // @codeCoverageIgnoreEnd

    public static function getLinks($entity, $codes, $inverse = false) {
        if (!is_array($codes)) {
            $codes = [$codes];
        }
        $objects = [];
        foreach ($codes as $code) {
            $objects = array_merge($objects, self::getLinksByCode($entity, $code, $inverse));
        }
        return $objects;
    }

    private static function getLinksByCode($entity, $code, $inverse) {
        $entity_id = (is_a($entity, 'Model_Entity')) ? $entity->id : $entity;
        $sql = self::$sqlSelect . ', e.name FROM model.link l JOIN model.entity e ON l.range_id = e.id
            WHERE l.property_id = :property_id AND l.domain_id = :entity_id ORDER BY e.name;';
        if ($inverse) {
            $sql = self::$sqlSelect . ', e.name FROM model.link l JOIN model.entity e ON l.domain_id = e.id
                WHERE l.property_id = :property_id AND l.range_id = :entity_id ORDER BY e.name;';
        }
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':property_id', Model_PropertyMapper::getByCode($code)->id);
        $statement->bindValue(':entity_id', $entity_id);
        $statement->execute();
        $objects = [];
        foreach ($statement->fetchAll() as $row) {
            $objects[] = self::populate($row);
        }
        return $objects;
    }

    private static function populate(array $row) {
        $link = new Model_Link();
        $link->id = $row['id'];
        $link->description = $row['description'];
        $property = Model_PropertyMapper::getById($row['property_id']);
        $link->property = $property;
        if (in_array($row['domain_id'], Zend_Registry::get('nodesIds'))) {
            $link->domain = Model_NodeMapper::getById($row['domain_id']);
        } else {
            $link->domain = Model_EntityMapper::getById($row['domain_id']);
        }
        if (in_array($row['range_id'], Zend_Registry::get('nodesIds'))) {
            $link->range = Model_NodeMapper::getById($row['range_id']);
        } else {
            $link->range = Model_EntityMapper::getById($row['range_id']);
        }
        return $link;
    }

    public static function linkExists($code, Model_Entity $domain, Model_Entity $range) {
        $sql = 'SELECT id FROM model.link
            WHERE property_id = :property_id AND domain_id = :domain_id AND range_id = :range_id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':property_id', Model_PropertyMapper::getByCode($code)->id);
        $statement->bindValue(':domain_id', $domain->id);
        $statement->bindValue(':range_id', $range->id);
        $statement->execute();
        if ($statement->fetchAll()) {
            return true;
        }
        return false;
    }

    public static function update(Model_Link $link) {
        $sql = 'UPDATE model.link SET (property_id, domain_id, range_id, description) =
            (:property_id, :domain_id, :range_id, :description) WHERE id = :id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':id', $link->id);
        $statement->bindValue(':property_id', $link->property->id);
        $statement->bindValue(':domain_id', $link->domain->id);
        $statement->bindValue(':range_id', $link->range->id);
        $statement->bindValue(':description', $link->description);
        $statement->execute();
        Model_UserLogMapper::insert('link', $link->id, 'update');
    }

    public static function insert($code, Model_Entity $domain, Model_Entity $range, $description = null) {
        $property = Model_PropertyMapper::getByCode($code);
        $whitelistDomains = Zend_Registry::get('config')->get('linkcheckIgnoreDomains')->toArray();
        if (!in_array($domain->class->code, $whitelistDomains)) {
            // @codeCoverageIgnoreStart
            // To do: remove CoverageIgnore after refactoring insert function
            if (!in_array($domain->class->code, $property->domain->getSubRecursive())) {
                $error = 'Wrong domain ' . $domain->class->code . ' for ' . $property->code;
                Model_LogMapper::log('error', 'model', $error);
                echo $error;
                exit;
            } else if (!in_array($range->class->code, $property->range->getSubRecursive())) {
                $error = 'Wrong range ' . $range->class->code . ' for ' . $property->code;
                Model_LogMapper::log('error', 'model', $error);
                echo $error;
                exit;
            }
            // @codeCoverageIgnoreEnd
        }
        $sql = 'INSERT INTO model.link (property_id, domain_id, range_id, description)
            VALUES (:property_id, :domain_id, :range_id, :description) RETURNING id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':property_id', $property->id);
        $statement->bindValue(':domain_id', $domain->id);
        $statement->bindValue(':range_id', $range->id);
        if ($description) {
            $statement->bindValue(':description', \Craws\FilterInput::filter($description, 'crm'));
        } else {
            $statement->bindValue(':description', null, PDO::PARAM_NULL);
        }
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $link = Model_LinkMapper::getById($result['id']);
        Model_LogMapper::log('info', 'insert', 'insert Link (' . $link->id . ')');
        Model_UserLogMapper::insert('link', $link->id, 'insert');
        return $link;
    }

    public static function delete(Model_Link $link) {
        foreach (['OA5', 'OA6'] as $code) {
            foreach (Model_LinkPropertyMapper::getLinks($link, $code) as $dateLink) {
                parent::deleteAbstract('model.entity', $dateLink->range->id);
            }
        }
        parent::deleteAbstract('model.link', $link->id);
    }

    public static function insertTypeLinks(Model_Entity $entity, Zend_Form $form, array $hierarchies) {
        foreach ($hierarchies as $hierarchy) {
            $idField = $hierarchy->nameClean . 'Id';
            if ($form->getValue($idField)) {
                foreach (explode(",", $form->getValue($idField)) as $id) {
                    Model_LinkMapper::insert('P2', $entity, Model_NodeMapper::getById($id));
                }
            } else if ($hierarchy->system) { // if its an empty system type, link the type root
                Model_LinkMapper::insert('P2', $entity, $hierarchy);
            }
        }
    }

}
