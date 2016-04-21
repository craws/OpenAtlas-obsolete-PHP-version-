<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_EntityMapper extends \Model_AbstractMapper {

    private static $sql = "
        SELECT e.id, e.class_id, e.name, e.description, e.created, e.modified, c.code,
            e.value_timestamp, e.value_integer,
            min(date_part('year', d1.value_timestamp)) AS first,
            max(date_part('year', d2.value_timestamp)) AS last
        FROM crm.entity e
        JOIN crm.class c ON e.class_id = c.id
        LEFT OUTER JOIN crm.link l ON e.id = l.domain_id
        LEFT OUTER JOIN crm.entity d1 ON l.range_id = d1.id
        LEFT OUTER JOIN crm.entity d2 ON l.range_id = d2.id
    ";

    public static function search($term, $codes, $description = false, $own = false) {
        if ($own) {
            self::$sql .= " LEFT JOIN web.user_log ul ON e.id = ul.table_id AND ul.table_name LIKE 'entity'";
        }
        $sql = self::$sql . " WHERE lower(e.name) LIKE :term AND ";
        if ($description) {
            $sql = self::$sql . " WHERE (lower(e.name) LIKE :term OR lower(e.description) LIKE :term) AND ";
        }
        if ($own) {
            $sql .= " ul.user_id = :user_id AND ";
        }
        $sql .= "e.class_id IN (SELECT id from crm.class WHERE code IN ('" . implode("', '", $codes) . "'))";
        $sql .= " GROUP BY e.id, c.code ORDER BY e.name";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':term', '%' . mb_strtolower($term) . '%');
        if ($own) {
            $statement->bindValue(':user_id', Zend_Registry::get('user')->id);
        }
        $statement->execute();
        $rows = $statement->fetchAll();
        $entitites = [];
        foreach ($rows as $row) {
            $entity = self::populate(new Model_Entity(), $row);
            switch ($entity->getClass()->code) {
                case 'E82':
                    $entityForAlias = Model_LinkMapper::getLinkedEntity($entity, 'P131', true);
                    if (!isset($entitites[$entityForAlias->id])) { // otherwise the one with dates would be overwriten
                        $entitites[$entityForAlias->id] = $entityForAlias;
                    }
                    break;
                case 'E41':
                    $entityForAlias = Model_LinkMapper::getLinkedEntity($entity, 'P1', true);
                    if (!isset($entitites[$entityForAlias->id])) { // otherwise the one with dates would be overwriten
                        $entitites[$entityForAlias->id] = $entityForAlias;
                    }
                    break;
                default:
                    $entitites[$entity->id] = $entity;
            }
        }
        return $entitites;
    }

    public static function getLatest($limit) {
        $codes = array_merge (
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
        $sql = self::$sql . ' WHERE e.id = :id GROUP BY e.id, c.code;';
        $row = parent::getRowById($sql, $id);
        return self::populate(new Model_Entity(), $row);
    }

    public static function getByCodes($code, $nodeRoot = false) {
        $codes = Zend_Registry::get('config')->get('code' . $code)->toArray();
        $sql = self::$sql . " WHERE c.code IN ('" . implode("', '", $codes) . "') GROUP BY e.id, c.code
            ORDER BY e.name;";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->execute();
        $entitites = [];
        foreach ($statement->fetchAll() as $row) {
            $entity = self::populate(new Model_Entity(), $row);
            if ($nodeRoot) {
                foreach (Model_LinkMapper::getLinkedEntities($entity, 'P2') as $node) {
                    if ($node->name == $nodeRoot) {
                        $entitites[] = $entity;
                    }
                }
                continue;
            }
            $entitites[] = $entity;

        }
        return $entitites;
    }

    public static function countByCodes($code) {
        $codes = Zend_Registry::get('config')->get('code' . $code)->toArray();
        $sql = "SELECT COUNT(*) AS count FROM crm.entity e JOIN crm.class c ON e.class_id = c.id
            WHERE c.code IN ('" . implode("', '", $codes) . "');";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->execute();
        $row = $statement->fetch();
        return $row['count'];
    }

    protected static function populate(Model_Entity $entity, array $row) {
        $classes = Zend_Registry::get('classes');
        $entity->id = $row['id'];
        $entity->setClass($classes[$row['class_id']]);
        $entity->name = $row['name'];
        $entity->description = $row['description'];
        $entity->date = parent::toZendDate($row['value_timestamp']);
        $entity->created = parent::toZendDate($row['created']);
        $entity->modified = parent::toZendDate($row['modified']);
        if (isset($row['first'])) {
            $entity->first = $row['first'];
        }
        if (isset($row['last'])) {
            $entity->last = $row['last'];
        }
        return $entity;
    }

    public static function insert($class, $name, $description = null, $date = null) {
        if (!is_numeric($class)) { // if $class was a string (code) get the id
            $class = Model_ClassMapper::getByCode($class)->id;
        }
        $sql = 'INSERT INTO crm.entity (class_id, name, description, value_timestamp)
            VALUES (:class_id, :name, :description, :value_timestamp) RETURNING id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':class_id', (int) $class);
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
        $entity = self::getById($result['id']);
        Model_UserLogMapper::insert('entity', $entity->id, 'insert');
        Model_LogMapper::log('info', 'insert', 'insert Entity (' . $entity->id . ')');
        return $entity;
    }

    public static function update(Model_Entity $entity) {
        $sql = 'UPDATE crm.entity SET (name, description) = (:name, :description) WHERE id = :id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':id', $entity->id);
        $statement->bindValue(':name', \Craws\FilterInput::filter($entity->name, 'crm'));
        if ($entity->description) {
            $statement->bindValue(':description', \Craws\FilterInput::filter($entity->description, 'crm'));
        } else {
            $statement->bindValue(':description', null, PDO::PARAM_NULL);
        }
        $statement->execute();
        Model_UserLogMapper::insert('entity', $entity->id, 'update');
    }

    public static function delete(Model_Entity $entity) {
        self::deleteDates($entity);
        foreach (Model_LinkMapper::getLinks($entity, ['P1', 'P53', 'P73', 'P131']) as $link) {
            parent::deleteAbstract('crm.entity', $link->getRange()->id);
        }
        parent::deleteAbstract('crm.entity', $entity->id);
    }

    public static function deleteDates(Model_Entity $entity) {
        foreach (Model_LinkMapper::getLinks($entity, ['OA1', 'OA2', 'OA3', 'OA4', 'OA5', 'OA6']) as $link) {
            parent::deleteAbstract('crm.entity', $link->getRange()->id);
        }
    }

    /* checks if an entry was modified since opening the update form */
    public static function checkIfModified(Model_Entity $entity, $startime) {
        $sql = "SELECT e.modified FROM crm.entity e WHERE id = :id;";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':id', $entity->id);
        $statement->execute();
        $row = $statement->fetch();
        $dateStarttime = new Zend_Date($startime, Zend_Date::TIMESTAMP);
        $dateModified = parent::toZendDate($row['modified']);
        var_dump($dateStarttime);
        var_dump($dateModified);
        if ($dateModified->isLater($dateStarttime)) {
        //if ($dateStarttime->isLater($dateModified)) {
            var_dump('yes');die;
            return true;
        }
        var_dump('no');die;
        return false;
    }

}
