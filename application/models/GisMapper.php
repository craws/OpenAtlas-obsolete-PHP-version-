<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_GisMapper extends Model_AbstractMapper {

    /* type, search?, new params */

    public static function getAll($objectId = 0) {
        $points = self::getPoints3($objectId);
        return $points;
    }

    public static function getPoints3($objectId) {
        $sql = "
            SELECT
                object.id AS object_id,
                point.id AS point_id,
                point.name AS point_name,
                point.description AS point_description,
                point.type,
                ST_AsGeoJSON(point.geom) AS geojson,
                object.name AS object_name,
                object.description AS object_description
            FROM model.entity place
            JOIN model.link l ON place.id = l.range_id
            JOIN model.entity object ON l.domain_id = object.id
            JOIN gis.point point ON place.id = point.entity_id
            WHERE
                place.class_id = (SELECT id FROM model.class WHERE code LIKE 'E53') AND
                l.property_id = (SELECT id FROM model.property WHERE code LIKE 'P53');
        ";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->execute();
        $gis['gisPointAll'] = '[';
        $gis['gisPointSelected'] = '[';
        foreach ($statement->fetchAll() as $row) {
            $point = '{"type":"Feature","geometry":' . $row['geojson'] . ',' .
                    '"properties":{' .
                        '"title": "' . str_replace('"', '\"', $row['object_name']) . '",' .
                        '"objectId": "' . $row['object_id'] . '",' .
                        '"objectDescription": "' . str_replace('"', '\"', $row['object_description']) . '",' .
                        '"id": "' . $row['point_id'] . '",' .
                        '"name": "' . str_replace('"', '\"', $row['point_name']) . '",' .
                        '"description": "' . str_replace('"', '\"', $row['point_description']) . '",' .
                        '"marker-color": "#fc4353",' .
                        '"siteType":" To do",' .
                        '"shapeType": "' . $row['type'] . '"' .
                    '}' .
                '},';
            if ($row['object_id'] == $objectId) {
                $gis['gisPointSelected'] .= $point;
            } else {
                $gis['gisPointAll'] .= $point;
            }
        }
        $gis['gisPointAll'] = rtrim($gis['gisPointAll'], ",");
        $gis['gisPointSelected'] = rtrim($gis['gisPointSelected'], ",");
        $gis['gisPointAll'] .= ']';
        $gis['gisPointSelected'] .= ']';
        return $gis;
    }

    public static function insertPoints(Model_Entity $place, $points) {
        if (!$points) {
            return;
        }
        foreach ($points as $point) {
            // TODO parameterize query
            $sql = "INSERT INTO gis.point (entity_id, name, description, type, geom)
                VALUES (
                    :entity_id,
                    :name,
                    :description,
                    :type,
                    st_geomfromtext('POINT('||" . $point->geometry->coordinates[0] . "||' '||" . $point->geometry->coordinates[1] . "||')',4326)
                );";
            $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
            $statement->bindValue(':entity_id', $place->id);
            $statement->bindValue(':name', $point->properties->name);
            $statement->bindValue(':description', $point->properties->description);
            $statement->bindValue(':type', $point->properties->shapeType);
            $statement->execute();
        }
        Model_UserLogMapper::insert('place', $place->id, 'insert ' . count($points) . 'point(s)');
    }

    public static function getPolygons(Model_Entity $object) {
        $sql = 'SELECT id, name, description, type, geom FROM gis.polygon WHERE entity_id = :entity_id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':entity_id', $object->id);
        $statement->execute();
        $result = $statement->fetch();
        if ($result) {
            $polygon['id'] = $result['id'];
            $polygon['name'] = $result['name'];
            $polygon['description'] = $result['description'];
            $polygon['type'] = $result['type'];
            $polygon['geom'] = $result['geom'];
            return $polygon;
        }
        return false;
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
    }

    // @codeCoverageIgnoreEnd

    public static function getByEntity(Model_Entity $entity) {
        $sql = 'SELECT st_x(st_transform(geom,4326)) as easting, st_y(st_transform(geom,4326)) as northing
            FROM gis.point WHERE entity_id = :entity_id;';
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

    public static function getPoints(Model_Entity $entity) {
        $sql = 'SELECT
            geom,
            name,
            description,
            type,
            st_x(st_transform(geom,4326)) as easting,
            st_y(st_transform(geom,4326)) as northing
            FROM gis.point WHERE entity_id = :entity_id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':entity_id', $entity->id);
        $statement->execute();
        $points = [];
        foreach ($statement->fetchAll() as $row) {
            $point = [];
            $point['name'] = $row['name'];
            $point['shapeType'] = $row['type'];
            $point['description'] = $row['description'];
            $point['geometryType'] = 'centerpoint';
            $point['easting'] = $row['easting'];
            $point['northing'] = $row['northing'];
            $points[] = $point;
        }
        return $points;
    }

    public static function getPoints2(Model_Entity $entity) {
        $sql = 'SELECT
            id,
            name,
            description,
            type,
            st_x(st_transform(geom,4326)) as easting,
            st_y(st_transform(geom,4326)) as northing
            FROM gis.point WHERE entity_id = :entity_id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':entity_id', $entity->id);
        $statement->execute();
        $points = '[';
        foreach ($statement->fetchAll() as $row) {
            /* $geometry = new stdClass();
              $geometry->type = 'Point';
              $geometry->coordinates = '[' . $row['easting'] . ',' . $row['northing'] . ']';
              $properties = new stdClass();
              $properties->uid = $row['id'];
              $properties->parentname = $entity->name;
              $properties->type = 'Centerpoint';
              $properties->title = $row['name'];
              $properties->description = $row['description'];
              $point = new stdClass();
              $point->type = 'Feature';
              $point->geometry = $geometry;
              $point->properties = $properties; */
            $point = '{
                    "type":"Feature",
                    "geometry":{
                        "type":"Point",
                        "coordinates":[' . $row['easting'] . ',' . $row['northing'] . ']
                    },
                    "properties":{
                        "uid":"' . $row['id'] . '",
                        "parentname":"' . $entity->name . '",
                        "type":"Centerpoint",
                        "title":"' . $row['name'] . '",
                        "description":"' . $row['description'] . '"
                    }
                },';
            $points .= $point;
        }
        return $points . ']';
    }

    public static function deleteByEntity($entity) {
        $sql = 'DELETE FROM gis.point WHERE entity_id = :entity_id;';
        //$sql .= 'DELETE FROM gis.polygon WHERE entity_id = :entity_id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue('entity_id', $entity->id);
        $statement->execute();
    }

}
