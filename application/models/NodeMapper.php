<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_NodeMapper extends Model_EntityMapper {

    public static function setAll() {
        $sql = "SELECT n.id, n.entity_id as id, n.multiple, n.system, n.is_extendable, n.is_directional,
            e.name, e.description, e.class_id, e.created, e.modified
            FROM web.node n JOIN model.entity e ON n.entity_id = e.id;";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->execute();
        $nodes = [];
        foreach ($statement->fetchAll() as $row) {
            $node = Model_EntityMapper::populate(new Model_Node(), $row);
            $node->multiple = $row['multiple'];
            $node->system = $row['system'];
            $node->extendable = $row['is_extendable'];
            $node->directional = $row['is_directional'];
            switch ($node->class->code) {
                case 'E55':
                    $node->propertyToEntity = 'P2';
                    $node->propertyToSuper = 'P127';
                    break;
                case 'E53':
                    $node->propertyToEntity = 'P89';
                    $node->propertyToSuper = 'P89';
                    break;
            }
            $nodes[$row['name']] = $node;
        }
        foreach ($nodes as $node) {
            self::addSubs($node);
        }
        Zend_Registry::set('nodes', $nodes);
    }

    private static function addSubs(Model_Node $node) {
        $sql = "SELECT e.id, e.name, e.description, e.class_id, e.created, e.modified
            FROM model.entity e JOIN model.link l ON e.id = l.domain_id
            WHERE
                l.range_id = :range_id AND
                l.property_id = :property_id AND
                e.name NOT LIKE 'Location of%';";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':range_id', $node->id);
        $statement->bindValue(':property_id', Model_PropertyMapper::getByCode($node->propertyToSuper)->id);
        $statement->execute();
        foreach ($statement->fetchAll() as $row) {
            $sub = Model_EntityMapper::populate(new Model_Node(), $row);
            $sub->superId = $node->superId ? $node->superId : $node->id;
            $sub->rootId = $node->rootId ? $node->rootId : $node->id;
            $sub->multiple = $node->multiple;
            $sub->system = $node->system;
            $sub->extendable = $node->extendable;
            $sub->directional = $node->directional;
            $sub->propertyToEntity = $node->propertyToEntity;
            $sub->propertyToSuper = $node->propertyToSuper;
            $node->subs[] = $sub;
            self::addSubs($sub);
        }
    }

    public static function getByNodeCategoryName($rootName, $name) {
        foreach (Zend_Registry::get('nodes') as $node) {
            if (mb_strtolower($node->name) == mb_strtolower($rootName)) {
                return self::getByNameRecursive($node, $name);
            }
        }
        Model_LogMapper::log('error', 'found no node for: ' . $rootName . ', ' . $name);
    }

    public static function getNodeByEntity($rootName, Model_Entity $entity) {
        $nodes = self::getNodesByEntity($rootName, $entity);
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

    public static function getNodesByEntity($rootName, Model_Entity $entity) {
        $nodes = [];
        foreach (Zend_Registry::get('nodes') as $node) {
            if (mb_strtolower($node->name) == mb_strtolower($rootName)) {
                foreach (Model_LinkMapper::getLinkedEntities($entity, $node->propertyToEntity) as $linkedEntity) {
                    $linkedNode = Model_NodeMapper::getById($linkedEntity->id);
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
        foreach (Zend_Registry::get('nodes') as $root) {
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

    public static function getTreeData($rootName, $selection = false) {
        if ($selection && !is_array($selection)) {
            $selection = [$selection];
        }
        $selectedIds = [];
        if ($selection && !empty($selection)) {
            foreach($selection as $selected) {
                $selectedIds[] = $selected->id;
            }
        }
        $item = self::getRootType($rootName);
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

    public static function getOptionsForSelect($rootName) {
        global $returnCandidates;
        $returnCandidates = [];
        foreach (Zend_Registry::get('nodes') as $node) {
            if (mb_strtolower($node->name) == mb_strtolower($rootName)) {
                $rootNode = $node;
                break;
            }
        }
        $options = self::getSuperCandidates($rootNode, 0, -1);
        unset($options[$rootNode->id]); // remove root node
        return $options;
    }

    public static function getRootType($rootName) {
        foreach (Zend_Registry::get('nodes') as $node) {
            if (mb_strtolower($node->name) == mb_strtolower($rootName)) {
                return $node;
            }
        }
    }

}
