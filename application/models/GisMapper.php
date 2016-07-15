<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_GisMapper extends Model_AbstractMapper {

    public static function insert(Model_Gis $gis) {
        $sql = 'INSERT INTO gis.centerpoint (entity_id, easting, northing)
            VALUES (:entity_id, :easting, :northing) RETURNING id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':entity_id', $gis->getEntity()->id);
        $statement->bindValue(':easting', $gis->easting);
        $statement->bindValue(':northing', $gis->northing);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        Model_UserLogMapper::insert('entity', $result['id'], 'gis insert');
    }

    public static function getJsonData($objects = false) {
        if (!$objects) {
            $objects = Model_EntityMapper::getByCodes('PhysicalObject');
        }
        $json['marker'] = '';
        $json['search'] = '';
        foreach ($objects as $object) {
            $place = Model_LinkMapper::getLinkedEntity($object, 'P53');
            $gis = Model_GisMapper::getByEntity($place);
            if ($gis) {
                $name = str_replace('"', '\"', $object->name);
                $type = Model_NodeMapper::getNodeByEntity('Site', $object);
                $typeName = str_replace('"', '\"', '');
                $description = str_replace('"', '\"', $object->description);
                $json['marker'] .= '{"type": "Feature","geometry":{"type": "Point","coordinates": [' . $gis->easting .
                    ',' . $gis->northing . ']},';
                $json['marker'] .= '"properties": {"title": "' . $name . '","description":"' .
                    $description . '",';
                $json['marker'] .= '"marker-color": "#fc4353","sitetype": "' . $typeName . '","uid": "' .
                    $object->id . '"}},';
                $json['search'] .= '{"label": "' . $name . '", "type": "' . $typeName . '", "uid": "' .
                    $object->id . '",';
                $json['search'] .= '"lat": "' . $gis->easting . '", "lon": "' . $gis->northing . '"},';
            }

        }
        if ($json['marker']) {
            return $json;
        }
        // @codeCoverageIgnoreStart
        // Ignore because to cumbersome to test closing bracket
    }
    // @codeCoverageIgnoreEnd


    public static function getByEntity(Model_Entity $entity) {
        $sql = 'SELECT easting, northing FROM gis.centerpoint WHERE entity_id = :entity_id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':entity_id', $entity->id);
        $statement->execute();
        $result = $statement->fetch();
        if ($result) {
            $gis = new Model_Gis();
            $gis->easting = $result['easting'];
            $gis->northing = $result['northing'];
            $gis->setEntity($entity);
            return $gis;
        }
        return false;
    }

    public static function deleteByEntity($entity) {
        $sql = 'DELETE FROM gis.centerpoint WHERE entity_id = :entity_id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue('entity_id', $entity->id);
        $statement->execute();
    }

}
