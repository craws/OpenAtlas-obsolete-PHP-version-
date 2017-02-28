<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_LinkMapper extends Model_AbstractMapper {

    public static function getById($id) {
        $sql = "
            SELECT l.id, l.property_id, l.domain_id, l.range_id, l.description, l.created, l.modified,
                (SELECT t.id FROM model.entity t
                    JOIN model.link_property lp ON t.id = lp.range_id AND lp.domain_id = l.id
                    WHERE lp.property_id = (SELECT id FROM model.property WHERE code = 'P2')
                ) AS type_id
            FROM model.link l
            WHERE l.id = :id;
        ";
        $row = parent::getRowById($sql, $id);
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
        }
        $error = 'Found ' . count($links) . ' ' . $code . ' links for (' . $entity->name . ')' . ' instead one.';
        if (is_a($entity, 'Model_Entity')) {
            $error = 'Found ' . count($links) . ' ' . $code . ' links for (' . $entity->id . ')' . ' instead one.';
        }
        Model_LogMapper::log('error', 'model', $error);
    }
    // @codeCoverageIgnoreEnd

    public static function getLinks($entity, $code, $inverse = false) {
        $entityId = (is_a($entity, 'Model_Entity')) ? $entity->id : $entity;
        $codes = (is_array($code)) ? $code : [$code];
        $first = ($inverse)?  'range' : 'domain';
        $second = ($inverse)?  'domain' : 'range';
        $sql = "
            SELECT l.id, l.property_id, l.domain_id, l.range_id, l.description, l.created, l.modified, e.name,
                min(date_part('year', d1.value_timestamp)) AS first,
                max(date_part('year', d2.value_timestamp)) AS last,
                (SELECT t.id FROM model.entity t
                    JOIN model.link_property lp ON t.id = lp.range_id AND lp.domain_id = l.id
                    WHERE lp.property_id = (SELECT id FROM model.property WHERE code = 'P2')
                ) AS type_id

            FROM model.link l
            JOIN model.entity e ON l." . $second . "_id = e.id
            JOIN model.property p ON l.property_id = p.id AND p.code IN ('" . implode("','", $codes) . "')

            LEFT JOIN model.link_property dl1 ON l.id = dl1.domain_id AND
                dl1.property_id IN (SELECT id FROM model.property WHERE code = 'OA5')
            LEFT JOIN model.entity d1 ON dl1.range_id = d1.id

            LEFT JOIN model.link_property dl2 ON l.id = dl2.domain_id
                AND dl2.property_id IN (SELECT id FROM model.property WHERE code = 'OA6')
            LEFT JOIN model.entity d2 ON dl2.range_id = d2.id

            WHERE l." . $first . "_id = :entity_id GROUP BY l.id, e.name ORDER BY e.name;";

        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':entity_id', $entityId);
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
        $link->first = (isset($row['first'])) ? $row['first'] : null;
        $link->last = (isset($row['last'])) ? $row['last'] : null;
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
        if (isset($row['type_id']) && $row['type_id']) {
            $link->type = Model_NodeMapper::getById($row['type_id']);
        }
        return $link;
    }

    /* domain and range parameter can be an id (integer) or a Model_Entity object */
    public static function linkExists($propertyCode, $domain, $range) {
        $domainId = (is_a($domain, 'Model_Entity')) ? $domain->id : $domain;
        $rangeId = (is_a($range, 'Model_Entity')) ? $range->id : $range;
        $sql = 'SELECT id FROM model.link
            WHERE property_id = :property_id AND domain_id = :domain_id AND range_id = :range_id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':property_id', Model_PropertyMapper::getByCode($propertyCode)->id);
        $statement->bindValue(':domain_id', $domainId);
        $statement->bindValue(':range_id', $rangeId);
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
    }

    public static function insert($propertyCode, $domain, $range, $description = null) {
        $property = Model_PropertyMapper::getByCode($propertyCode);
        self::checkLink($property, $domain, $range);
        $domainId = (is_a($domain, 'Model_Entity')) ? $domain->id : $domain;
        $rangeId = (is_a($range, 'Model_Entity')) ? $range->id : $range;
        $sql = 'INSERT INTO model.link (property_id, domain_id, range_id, description)
            VALUES (:property_id, :domain_id, :range_id, :description) RETURNING id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':property_id', $property->id);
        $statement->bindValue(':domain_id', $domainId);
        $statement->bindValue(':range_id', $rangeId);
        if ($description) {
            $statement->bindValue(':description', \Craws\FilterInput::filter($description, 'crm'));
        } else {
            $statement->bindValue(':description', null, PDO::PARAM_NULL);
        }
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result['id'];
    }

    public static function delete(Model_Link $link) {
        foreach (Model_LinkPropertyMapper::getLinks($link, ['OA5', 'OA6']) as $dateLink) {
            parent::deleteAbstract('model.entity', $dateLink->range->id);
        }
        parent::deleteAbstract('model.link', $link->id);
    }

    public static function insertTypeLinks($entity, Zend_Form $form, array $hierarchies) {
        foreach ($hierarchies as $hierarchy) {
            $idField = $hierarchy->nameClean . 'Id';
            if ($form->getValue($idField)) {
                foreach (explode(",", $form->getValue($idField)) as $id) {
                    Model_LinkMapper::insert('P2', $entity, $id);
                }
            } else if ($hierarchy->system) { // if its an empty system type, link the type root
                Model_LinkMapper::insert('P2', $entity, $hierarchy);
            }
        }
    }

    private static function checkLink($property, $domainParam, $rangeParam) {
        $whitelistDomains = Zend_Registry::get('config')->get('linkcheckIgnoreDomains')->toArray();
        $domain = (is_a($domainParam, 'Model_Entity')) ? $domainParam : Model_EntityMapper::getById($domainParam);
        $range = (is_a($rangeParam, 'Model_Entity')) ? $rangeParam : Model_EntityMapper::getById($rangeParam);
        // cannot test when using exit
        // @codeCoverageIgnoreStart
        if (!in_array($domain->class->code, $whitelistDomains)) {
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
        }
        // @codeCoverageIgnoreEnd
    }

}
