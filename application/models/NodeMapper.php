<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_NodeMapper extends Model_EntityMapper {

    public static function setAll() {
        foreach (['type', 'place', 'event'] as $hierarchy) {
            Zend_Registry::set($hierarchy, self::getAll($hierarchy));
        }
    }

    private static function getAll($hierarchy) {
        switch ($hierarchy) {
            case 'place':
                $propertyToEntity = 'P89';
                $propertyToSuper = 'P89';
                $sql = "
                    SELECT e.id, e.class_id, e.name, e.description, e.created, e.modified, c.code,
                      e.value_timestamp, e.value_integer, l.range_id, l2.property_id
                    FROM model.entity e
                    LEFT OUTER JOIN model.link l ON e.id = l.domain_id
                    LEFT OUTER JOIN model.link l2 ON e.id = l2.domain_id
                    JOIN model.class c ON e.class_id = c.id
                    WHERE c.code = 'E53' AND e.name NOT LIKE 'Location of%'
                    ORDER BY e.name;";
                break;
            case 'event':
                $propertyToEntity = 'P117';
                $propertyToSuper = 'P117';
                $sql = "
                    SELECT e.id, e.class_id, e.name, e.description, e.created, e.modified, c.code,
                      e.value_timestamp, e.value_integer, l.range_id
                    FROM model.entity e
                    LEFT JOIN model.link l ON e.id = l.domain_id AND l.property_id = :property_id
                    JOIN model.class c ON e.class_id = c.id
                    WHERE c.code IN ('" . implode("', '", Zend_Registry::get('config')->get('codeEvent')->toArray()) . "')
                    ORDER BY e.name;";
                break;
            case 'type':
                $propertyToEntity = 'P2';
                $propertyToSuper = 'P127';
                $sql = "
                    SELECT e.id, e.class_id, e.name, e.description, e.created, e.modified, c.code,
                      e.value_timestamp, e.value_integer, l.range_id
                    FROM model.entity e
                    LEFT JOIN model.link l ON e.id = l.domain_id
                    JOIN model.class c ON e.class_id = c.id
                    WHERE c.code = 'E55'
                    ORDER BY e.name;";
                break;
        }
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        if ($hierarchy == 'event') {
            $statement->bindValue(':property_id', Model_PropertyMapper::getByCode($propertyToSuper)->id);
        }
        $statement->execute();
        $nodes = [];
        foreach ($statement->fetchAll() as $row) {
            $node = parent::populate(new Model_Node(), $row);
            $node->superId = $row['range_id'];
            $node->propertyToEntity = $propertyToEntity;
            $node->propertyToSuper = $propertyToSuper;
            $nodes[$row['id']] = $node;
        }
        return self::buildTree($nodes);
    }

    public static function getByNodeCategoryName($hierarchy, $rootName, $name) {
        foreach (Zend_Registry::get($hierarchy) as $node) {
            if (mb_strtolower($node->name) == mb_strtolower($rootName)) {
                return self::getByNameRecursive($node, $name);
            }
        }
        Model_LogMapper::log('error', 'found no node for: ' . $hierarchy . ', ' . $rootName . ', ' . $name);
    }

    public static function getNodeByEntity($hierarchy, $rootName, Model_Entity $entity) {
        $nodes = self::getNodesByEntity($hierarchy, $rootName, $entity);
        switch (count($nodes)) {
            case 0:
                return false;
            case 1:
                return $nodes[0];
            // @codeCoverageIgnoreStart
        }
        $error = 'Found ' . count($nodes) . ' ' . $rootName . ' nodes for Entity (' . $entity->id . ') instead of one.';
        Model_LogMapper::log('error', 'model', $error);
    }

    // @codeCoverageIgnoreEnd

    public static function getNodesByEntity($hierarchy, $rootName, Model_Entity $entity) {
        $nodes = [];
        foreach (Zend_Registry::get($hierarchy) as $node) {
            if (mb_strtolower($node->name) == mb_strtolower($rootName)) {
                foreach (Model_LinkMapper::getLinkedEntities($entity, $node->propertyToEntity) as $linkedNode) {
                    if ($linkedNode->rootId == $node->id || $linkedNode->id == $node->id) {
                        $nodes[] = $linkedNode;
                    }
                }
            }
        }
        return $nodes;
    }

    private static function getByNameRecursive($node, $name) {
        if (mb_strtolower($node->name) == mb_strtolower($name)) {
            return $node;
        }
        foreach ($node->subs as $sub) {
            if (mb_strtolower($sub->name) == mb_strtolower($name)) {
                return $sub;
            }
            foreach ($sub->subs as $subSub) {
                $subNode = self::getByNameRecursive($subSub, $name);
                if ($subNode) {
                    return $subNode;
                }
            }
        }
        return false;
    }

    public static function getById($id) {
        switch (Model_EntityMapper::getById($id)->getClass()->code) {
            case 'E6':
            case 'E7':
            case 'E8':
            case 'E12':
                $category = 'event';
                break;
            case 'E53':
                $category = 'place';
                break;
            case 'E55':
                $category = 'type';
                break;
        }
        foreach (Zend_Registry::get($category) as $root) {
            $node = self::recursiveSearchId($root, $id);
            if ($node) {
                return $node;
            }
        }
    }

    private static function recursiveSearchId($node, $id) {
        if ($node->id == $id) {
            return $node;
        }
        foreach ($node->subs as $sub) {
            $foundNode = self::recursiveSearchId($sub, $id);
            if ($foundNode) {
                return $foundNode;
            }
        }
    }

    public static function getSuperCandidates(Model_Node $node, $requestId = 0, $level = 0) {
        global $returnCandidates;
        $spacer = '';
        if ($level > 0) {
            $spacer = str_repeat('-', $level);
        }
        $returnCandidates[$node->id] = $spacer . $node->name;
        $level++;
        foreach ($node->subs as $sub) {
            if ($sub->id == $requestId) {
                continue;
            }
            $returnCandidates[$sub->id] = str_repeat('-', $level) . $sub->name;
            self::getSuperCandidates($sub, $requestId, $level);
        }
        return $returnCandidates;
    }

    private static function buildTree(array $nodeArray) {
        $expandableArray = Zend_Registry::get('config')->get('nodeExpandable')->toArray();
        $rootNodes = [];
        foreach ($nodeArray as $node) {
            if (!$node->superId) {
                if (array_key_exists($node->name, $expandableArray)) {
                    $node->expandable = true;
                    if ($expandableArray[$node->name]) {
                        $node->directed = true;
                    }
                }
                $rootNodes[] = $node;
            }
        }
        foreach ($rootNodes as $rootNode) {
            self::addSubs($rootNode, $nodeArray);
        }
        return $rootNodes;
    }

    private static function addSubs(Model_Node $super, array $nodeArray) {
        foreach ($nodeArray as $node) {
            if ($node->superId == $super->id) {
                $node->expandable = $super->expandable;
                $node->directed = $super->directed;
                $node->superId = $super->id;
                $node->rootId = $super->id;
                if ($super->rootId) {
                    $node->rootId = $super->rootId;
                }
                self::addSubs($node, $nodeArray);
                $super->addSub($node);
            }
        }
    }

    public static function getTreeData($hierarchy, $rootName, $selection = false) {
        if ($selection && !is_array($selection)) {
            $selection = [$selection];
        }
        $selectedIds = [];
        if ($selection && !empty($selection)) {
            foreach($selection as $selected) {
                $selectedIds[] = $selected->id;
            }

        }
        $item = self::getRootType($hierarchy, $rootName);
        $data = "{'data':[" . self::walkTree($item, $selectedIds) . "]}";
        return $data;
    }

    private static function walkTree($item, $selectedIds) {
        $text = '';
        if ($item->rootId) { // only if not root item
            $text = "{'text':'" . str_replace("'", "\'", $item->name) . "', 'id':'" . $item->id . "',";
            if (in_array($item->id, $selectedIds)) {
                $text .= "'state' : {'selected' : true},";
            }
            if ($item->subs) {
                $text .= "'children' : [";
                foreach ($item->subs as $sub) {
                    $text .= self::walkTree($sub, $selectedIds);
                }
                $text .= "]";
            }
            $text .= "},";
            return $text;
        }
        if ($item->subs) {
            foreach ($item->subs as $sub) {
                $text .= self::walkTree($sub, $selectedIds);
            }
        }
        return $text;
    }

    public static function getOptionsForSelect($hierarchy, $rootName) {
        global $returnCandidates;
        $returnCandidates = [];
        foreach (Zend_Registry::get($hierarchy) as $node) {
            if (mb_strtolower($node->name) == mb_strtolower($rootName) ||
                mb_strtolower($node->name) == mb_strtolower(Zend_Registry::get('event')[0]->name)) {
                $rootNode = $node;
                break;
            }
        }
        $options = self::getSuperCandidates($rootNode, 0, -1);
        unset($options[$rootNode->id]); // remove root node
        return $options;
    }

    public static function getRootType($hierarchy, $rootName) {
        foreach (Zend_Registry::get($hierarchy) as $node) {
            if (mb_strtolower($node->name) == mb_strtolower($rootName) ||
                mb_strtolower($node->name) == mb_strtolower(Zend_Registry::get('event')[0]->name)) {
                return $node;
            }
        }
    }

}
