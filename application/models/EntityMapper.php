<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_EntityMapper extends \Model_AbstractMapper {

    private static $sql = "
        SELECT
            e.id, e.class_id, e.name, e.description, e.created, e.modified, c.code,
            e.value_timestamp, e.value_integer, string_agg(CAST(t.id AS text), ',') AS types,
            min(date_part('year', d1.value_timestamp)) AS first,
            max(date_part('year', d2.value_timestamp)) AS last

        FROM model.entity e

        JOIN model.class c ON e.class_id = c.id

        LEFT JOIN model.link tl ON e.id = tl.domain_id
        LEFT JOIN model.entity t ON tl.range_id = t.id AND tl.property_id = (SELECT id FROM model.property WHERE name LIKE 'has type')

        LEFT JOIN model.link dl1 ON e.id = dl1.domain_id AND dl1.property_id IN (SELECT id FROM model.property WHERE code in ('OA1', 'OA3'))
        LEFT JOIN model.entity d1 ON dl1.range_id = d1.id

        LEFT JOIN model.link dl2 ON e.id = dl2.domain_id AND dl2.property_id IN (SELECT id FROM model.property WHERE code in ('OA2', 'OA4'))
        LEFT JOIN model.entity d2 ON dl2.range_id = d2.id
    ";

    public static function search($term, $codes, $description = false, $own = false) {
        $sql = self::$sql;
        $sql .= ($own) ? " LEFT JOIN web.user_log ul ON e.id = ul.table_id AND ul.table_name LIKE 'entity'" : '';
        $sql .= " WHERE lower(e.name) LIKE :term ";
        $sql .= ($description) ? " OR lower(e.description) LIKE :term AND " : " AND ";
        $sql .= ($own) ? " ul.user_id = :user_id AND " : '';
        $sql .= "e.class_id IN (SELECT id from model.class WHERE code IN ('" . implode("', '", $codes) . "'))";
        $sql .= " GROUP BY e.id, c.code ORDER BY e.name";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':term', '%' . mb_strtolower($term) . '%');
        if ($own) {
            $statement->bindValue(':user_id', Zend_Registry::get('user')->id);
        }
        $statement->execute();
        $entitites = [];
        foreach ($statement->fetchAll() as $row) {
            $entity = self::populate(new Model_Entity(), $row);
            switch ($entity->class->code) {
                // @codeCoverageIgnoreStart
                case 'E82':
                    $entityForAlias = $entity->getLinkedEntity('P131', true);
                    if (!isset($entitites[$entityForAlias->id])) { // otherwise the one with dates would be overwriten
                        $entitites[$entityForAlias->id] = $entityForAlias;
                    }
                    break;
                case 'E41':
                    $entityForAlias = $entity->getLinkedEntity('P1', true);
                    if (!isset($entitites[$entityForAlias->id])) { // otherwise the one with dates would be overwriten
                        $entitites[$entityForAlias->id] = $entityForAlias;
                    }
                    break;
                // @codeCoverageIgnoreEnd
                default:
                    $entitites[$entity->id] = $entity;
            }
        }
        return $entitites;
    }

    public static function getLatest($limit) {
        $codes = array_merge(
            Zend_Registry::get('config')->get('code' . 'Source')->toArray(),
            Zend_Registry::get('config')->get('code' . 'Event')->toArray(),
            Zend_Registry::get('config')->get('code' . 'Actor')->toArray(),
            Zend_Registry::get('config')->get('code' . 'PhysicalObject')->toArray(),
            Zend_Registry::get('config')->get('code' . 'Reference')->toArray()
        );
        $sql = self::$sql . "WHERE c.code IN ('" . implode("', '", $codes) . "') GROUP BY e.id, c.code
            ORDER BY e.created DESC LIMIT :limit;";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':limit', $limit);
        $statement->execute();
        $entitites = [];
        foreach ($statement->fetchAll() as $row) {
            $entitites[] = self::populate(new Model_Entity(), $row);
        }
        return $entitites;
    }

    public static function getById($id) {
        if (in_array($id, Zend_Registry::get('nodesIds'))) {
            return Model_NodeMapper::getById($id);
        }
        $sql = self::$sql . ' WHERE e.id = :id GROUP BY e.id, c.code;';
        $row = parent::getRowById($sql, $id);
        return self::populate(new Model_Entity(), $row);
    }

    public static function getByCodes($code) {
        $codes = Zend_Registry::get('config')->get('code' . $code)->toArray();
        $sql = self::$sql . " WHERE c.code IN ('" . implode("', '", $codes) . "') GROUP BY e.id, c.code
            ORDER BY e.name;";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->execute();
        $entitites = [];
        foreach ($statement->fetchAll() as $row) {
            $entity = self::populate(new Model_Entity(), $row);
            if ($code == 'Source') {
                if (isset($entity->types['Linguistic object classification']) &&
                    $entity->types['Linguistic object classification'][0]->name == 'Source Content') {
                        $entitites[] = $entity;
                }
                continue;
            }
            $entitites[] = $entity;
        }
        return $entitites;
    }

    public static function countByCodes($code) {
        $codes = Zend_Registry::get('config')->get('code' . $code)->toArray();
        $sql = "SELECT COUNT(*) AS count FROM model.entity e JOIN model.class c ON e.class_id = c.id
            WHERE c.code IN ('" . implode("', '", $codes) . "');";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->execute();
        $row = $statement->fetch();
        return $row['count'];
    }

    protected static function populate(Model_Entity $entity, array $row) {
        $classes = Zend_Registry::get('classes');
        $entity->id = $row['id'];
        $entity->class = $classes[$row['class_id']];
        $entity->name = $row['name'];
        $entity->description = $row['description'];
        $entity->date = (isset($row['value_timestamp'])) ? parent::toZendDate($row['value_timestamp']) : null;
        $entity->created = parent::toZendDate($row['created']);
        $entity->modified = parent::toZendDate($row['modified']);
        $entity->first = (isset($row['first'])) ? $row['first'] : null;
        $entity->last = (isset($row['last'])) ? $row['last'] : null;
        $types = [];
        if (isset($row['types']) && $row['types']) {
            foreach (array_unique(explode(',', $row['types'])) as $type_id) {
                $type = Model_NodeMapper::getById($type_id);
                $root_name = ($type->rootId) ? Model_NodeMapper::getById($type->rootId)->name : $type->name;
                $types[$root_name][] = $type;
            }
            $entity->types = $types;
        }
        return $entity;
    }

    public static function insert($class, $name, $description = null, $date = null) {
        $classId = (is_numeric($class)) ? $class : Model_ClassMapper::getByCode($class)->id;
        $sql = 'INSERT INTO model.entity (class_id, name, description, value_timestamp)
            VALUES (:class_id, :name, :description, :value_timestamp) RETURNING id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':class_id', (int) $classId);
        if ($description) {
            $statement->bindValue(':description', \Craws\FilterInput::filter($description, 'crm'));
        } else {
            $statement->bindValue(':description', null, PDO::PARAM_NULL);
        }
        if ($date) {
            $zendDate = new Zend_Date($date, null, 'en');
            $dbDate = parent::toDbDate($zendDate);
            $statement->bindValue(':value_timestamp', $dbDate);
            $statement->bindValue(':name', $dbDate);
        } else {
            $statement->bindValue(':name', \Craws\FilterInput::filter($name, 'crm'));
            $statement->bindValue(':value_timestamp', null, PDO::PARAM_NULL);
        }
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result['id'];
    }

    public static function update(Model_Entity $entity) {
        $sql = 'UPDATE model.entity SET (name, description) = (:name, :description) WHERE id = :id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':id', $entity->id);
        $statement->bindValue(':name', \Craws\FilterInput::filter($entity->name, 'crm'));
        if ($entity->description) {
            $statement->bindValue(':description', \Craws\FilterInput::filter($entity->description, 'crm'));
        } else {
            $statement->bindValue(':description', null, PDO::PARAM_NULL);
        }
        $statement->execute();
    }

    public static function delete(Model_Entity $entity) {
        self::deleteDates($entity);
        foreach ($entity->getLinks(['P1', 'P53', 'P73', 'P131']) as $link) {
            parent::deleteAbstract('model.entity', $link->range->id);
        }
        parent::deleteAbstract('model.entity', $entity->id);
    }

    public static function deleteDates(Model_Entity $entity) {
        foreach ($entity->getLinks(['OA1', 'OA2', 'OA3', 'OA4', 'OA5', 'OA6']) as $link) {
            parent::deleteAbstract('model.entity', $link->range->id);
        }
    }

    public static function checkIfModified(Model_Entity $entity, $modified) {
        if ($entity->modified && $entity->modified->getTimestamp() > $modified) {
            return true; /* return true if an entry was modified since opening the update form */
        }
    }

    public static function getRootEvent() {
        $sql = "
            SELECT e.id, e.class_id, e.name, e.description, e.created, e.modified, c.code
            FROM model.entity e JOIN model.class c ON e.class_id = c.id
            WHERE e.name LIKE :name ORDER BY id ASC LIMIT 1;";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':name', Zend_Registry::get('config')->get('eventRootName'));
        $statement->execute();
        return self::populate(new Model_Entity(), $statement->fetch());
    }

}
