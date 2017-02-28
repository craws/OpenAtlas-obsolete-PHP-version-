<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_GisMapper extends Model_AbstractMapper {

    public static function getAll($objectIdsParam = []) {
        $all['point'] = [];
        $all['polygon'] = [];
        $selected['point'] = [];
        $selected['polygon'] = [];
        $selected['polygonPoint'] = [];
        $objectIds = (is_array($objectIdsParam)) ? $objectIdsParam : [$objectIdsParam];
        foreach (['point', 'polygon'] as $shape) {
            $polygonPointSql = ($shape == 'polygon') ? " (SELECT ST_AsGeoJSON(ST_PointOnSurface(p.geom)) FROM gis.polygon p WHERE id = polygon.id) AS polygon_point, " : '';
            $sql = "
                SELECT
                    object.id AS object_id,
                    " . $shape . ".id,
                    " . $shape . ".name,
                    " . $shape . ".description,
                    " . $shape . ".type,
                    ST_AsGeoJSON(" . $shape . ".geom) AS geojson, " . $polygonPointSql . "
                    object.name AS object_name,
                    object.description AS object_description,
                    (SELECT COUNT(*) FROM gis.point point2 WHERE " . $shape . ".entity_id = point2.entity_id) AS point_count,
                    (SELECT COUNT(*) FROM gis.polygon polygon2 WHERE " . $shape . ".entity_id = polygon2.entity_id) AS polygon_count,
                    array_to_json(ARRAY(
                        SELECT l.range_id
                        FROM model.link l
                        JOIN model.property p ON l.property_id = p.id
                        WHERE l.domain_id = object.id AND p.code = 'P2'
                    )) AS node_ids
                FROM model.entity place
                JOIN model.link l ON place.id = l.range_id
                JOIN model.entity object ON l.domain_id = object.id
                JOIN gis." . $shape . " " . $shape . " ON place.id = " . $shape . ".entity_id
                WHERE
                    place.class_id = (SELECT id FROM model.class WHERE code = 'E53') AND
                    l.property_id = (SELECT id FROM model.property WHERE code = 'P53');
                ";
            $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
            $statement->execute();
            foreach ($statement->fetchAll() as $row) {
                $type = '';
                foreach (json_decode($row['node_ids']) as $nodeId) {
                    $node = Model_NodeMapper::getById($nodeId);
                    if ($node->rootId && Model_NodeMapper::getById($node->rootId)->name == 'Site') {
                        $type = $node->name;
                    }
                }
                $item = [
                    'type' => 'Feature',
                    'geometry' => json_decode($row['geojson']),
                    'properties' => [
                        'title' => str_replace('"', '\"', $row['object_name']),
                        'objectId' => (int) $row['object_id'],
                        'objectDescription' => str_replace('"', '\"', $row['object_description']),
                        'id' => (int) $row['id'],
                        'name' => str_replace('"', '\"', $row['name']),
                        'description' => str_replace('"', '\"', $row['description']),
                        'siteType' => $type,
                        'shapeType' => $row['type'],
                        'count' => $row['point_count'] + $row['polygon_count']
                    ]
                ];
                if (in_array($row['object_id'], $objectIds)) {
                    $selected[$shape][] = $item;
                } else {
                    $all[$shape][] = $item;
                }
                if (isset($row['polygon_point'])) {
                    $item['geometry'] = json_decode($row['polygon_point']);
                    if (in_array($row['object_id'], $objectIds)) {
                        $selected['polygonPoint'][] = $item;
                    } else {
                        $all['point'][] = $item;
                    }
                }
            }
        }
        $gis['gisPointAll'] = json_encode($all['point']);
        $gis['gisPointSelected'] = json_encode($selected['point']);
        $gis['gisPolygonAll'] = json_encode($all['polygon']);
        $gis['gisPolygonSelected'] = json_encode($selected['polygon']);
        $gis['gisPolygonPointSelected'] = json_encode($selected['polygonPoint']);
        return $gis;
    }

    public static function insert(Model_Entity $place, Zend_Form $form) {
        foreach (['point', 'polygon'] as $shape) {
            $fieldName = 'gis' . ucfirst($shape) . 's';
            foreach (json_decode($form->$fieldName->getValue()) as $item) {
                $sql = "INSERT INTO gis." . $shape . " (entity_id, name, description, type, geom)
                    VALUES (
                        :entity_id,
                        :name,
                        :description,
                        :type,
                        ST_SetSRID(ST_GeomFromGeoJSON(:geojson),4326)
                    );";
                $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
                $statement->bindValue(':entity_id', $place->id);
                $statement->bindValue(':name', $item->properties->name);
                $statement->bindValue(':description', $item->properties->description);
                $statement->bindValue(':type', $item->properties->shapeType);
                $statement->bindValue(':geojson', json_encode($item->geometry));
                $statement->execute();
            }
        }
    }

    public static function deleteByEntity($entity) {
        $sql = 'DELETE FROM gis.point WHERE entity_id = :entity_id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue('entity_id', $entity->id);
        $statement->execute();
        $sqlPolygon = 'DELETE FROM gis.polygon WHERE entity_id = :entity_id;';
        $statementPolygon = Zend_Db_Table::getDefaultAdapter()->prepare($sqlPolygon);
        $statementPolygon->bindValue('entity_id', $entity->id);
        $statementPolygon->execute();
    }

}
