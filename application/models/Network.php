<?php

/* Copyright 2017 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_Network {

    public static function getData() {
        $namespace = new Zend_Session_Namespace('Default');
        $classes = [];
        foreach ($namespace->network['classes'] as $code => $params) {
            if ($params['active']) {
                $classes[] = $code;
            }
        }
        $properties = [];
        foreach ($namespace->network['properties'] as $code => $params) {
            if ($params['active']) {
                $properties[] = $code;
            }
        }
        $sql = "
            SELECT l.domain_id, l.range_id
            FROM model.link l
            JOIN model.property p ON l.property_id = p.id
            WHERE p.code IN ('" . implode("','", $properties) . "');";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->execute();
        $entites = [];
        $edges = '';
        foreach ($statement->fetchAll() as $row) {
            $edges .= "{'source': '" . $row['domain_id'] . "', 'target': '" . $row['range_id'] . "' },";
            $entites[] = $row['domain_id'];
            $entites[] = $row['range_id'];
        }
        $edges = " links: [" . $edges . "]";
        $sql = "
            SELECT e.id, e.class_id, e.name, c.code
            FROM model.entity e
            JOIN model.class c ON e.class_id = c.id
            WHERE c.code IN ('" . implode("','", array_keys($namespace->network['classes'])) . "');";
        $statement2 = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement2->execute();
        $nodes = '';
        $entitiesAlready = [];
        foreach ($statement2->fetchAll() as $row) {
            if ($row['name'] == 'History of the World') {
                continue;
            }
            if ($row['code'] == 'E53') {
                if (0 !== strpos($row['name'], 'Location of ')) {
                    continue;
                }
                $row['name'] = str_replace('Location of ', '', $row['name']);
            }
            if ($namespace->network['options']['show orphans'] || in_array($row['id'], $entites)) {
                $entitiesAlready[] = $row['id'];
                $nodes .= "{'id':'" . $row['id'] . "', 'name':'" . str_replace("'", "", $row['name']) . "', 'color':'";
                $nodes .= $namespace->network['classes'][$row['code']]['color'] . "'},";
            }
        }
        // Get elements that of links which wasn't present in class selection
        $arrayDiff = array_unique(array_diff($entites, $entitiesAlready));
        if ($arrayDiff) {
            $sql = "
                SELECT e.id, e.class_id, e.name, c.code
                FROM model.entity e
                JOIN model.class c ON e.class_id = c.id
                WHERE e.id IN (" . implode(",", $arrayDiff) . ");";
            $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
            $statement->execute();
            foreach ($statement->fetchAll() as $row) {
                $nodes .= "{'id':'" . $row['id'] . "', 'name':'" . str_replace("'", "", $row['name']) . "', 'color':'";
                $nodes .= $namespace->network['classes'][$row['code']]['color'] . "'},";
            }
        };
        return "graph = {'nodes': [" . $nodes . "], " . $edges . "};";
    }
}
