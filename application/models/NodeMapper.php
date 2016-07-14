<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_NodeMapper extends Model_EntityMapper {

    public static function registerHierarchies() {
        $sqlForms = "SELECT f.id, f.name, f.extendable,
            (SELECT ARRAY(SELECT h.id FROM web.hierarchy h JOIN web.hierarchy_form hf ON h.id = hf.hierarchy_id
            WHERE hf.form_id = f.id )) AS hierarchy_ids
            FROM web.form f ORDER BY name ASC;";
        $statementForms = Zend_Db_Table::getDefaultAdapter()->prepare($sqlForms);
        $statementForms->execute();
        $forms = [];
        foreach ($statementForms->fetchAll() as $row) {
            $forms[$row['name']]['id'] = $row['id'];
            $forms[$row['name']]['name'] = $row['name'];
            $forms[$row['name']]['hierarchyIds'] = str_getcsv(trim($row['hierarchy_ids'], '{}'));
            $forms[$row['name']]['extendable'] = $row['extendable'];
        }
        Zend_Registry::set('forms', $forms);
        $sql = "SELECT h.id, h.multiple, h.system, h.extendable, h.directional,
            e.name, e.description, e.class_id, e.created, e.modified
            FROM web.hierarchy h JOIN model.entity e ON h.id = e.id;";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->execute();
        $nodes = [];
        $nodeIds = [];
        foreach ($statement->fetchAll() as $row) {
            $node = Model_EntityMapper::populate(new Model_Node(), $row);
            $node->multiple = $row['multiple'];
            $node->system = $row['system'];
            $node->extendable = $row['extendable'];
            $node->directional = $row['directional'];
            $node->nameClean = \Craws\FilterInput::filter($row['name'], 'node');
            foreach ($forms as $form) {
                if (in_array($node->id, $form['hierarchyIds'])) {
                    $node->forms[] = $form;
                }
            }
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
            $nodeIds[] = $node->id;
        }
        foreach ($nodes as $node) {
            self::addSubs($node, $nodeIds);
        }
        Zend_Registry::set('nodes', $nodes);
        Zend_Registry::set('nodesIds', $nodeIds); // nodeIds array to identify nodes in LinkMapper
    }

    private static function addSubs(Model_Node $node, &$nodeIds) {
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
            $sub->superId = $node->id;
            $sub->rootId = $node->rootId ? $node->rootId : $node->id;
            $sub->multiple = $node->multiple;
            $sub->system = $node->system;
            $sub->extendable = $node->extendable;
            $sub->directional = $node->directional;
            $sub->propertyToEntity = $node->propertyToEntity;
            $sub->propertyToSuper = $node->propertyToSuper;
            $node->subs[] = $sub;
            $nodeIds[] = $sub->id;
            self::addSubs($sub, $nodeIds);
        }
    }

    /* This method is not reliable for types which are editable, use only for system types and tests! */
    public static function getByNodeCategoryName($rootName, $name) {
        foreach (Zend_Registry::get('nodes') as $node) {
            if (mb_strtolower($node->name) == mb_strtolower($rootName)) {
                return self::getByNameRecursive($node, $name);
            }
        }
        Model_LogMapper::log('error', 'found no node for: ' . $rootName . ', ' . $name);
    }

    // @codeCoverageIgnoreStart
    // Ignore coverage because cumbersome to test failures
    public static function getNodeByEntity($rootName, Model_Entity $entity) {
        $nodes = self::getNodesByEntity($rootName, $entity);
        switch (count($nodes)) {
            case 0:
                return false;
            case 1:
                return $nodes[0];
        }
        $error = 'Found ' . count($nodes) . ' ' . $rootName . ' nodes for Entity (' . $entity->id . ') instead of one.';
        Model_LogMapper::log('error', 'model', $error);
    }
    // @codeCoverageIgnoreEnd

    public static function getNodesByEntity($rootName, $entity) {
        $nodes = [];
        foreach (Zend_Registry::get('nodes') as $node) {
            if (\Craws\FilterInput::filter($node->name, 'node') == \Craws\FilterInput::filter($rootName, 'node')) {
                $realEntity = $entity;
                /* if its a place we need the object for locations */
                if (in_array($node->name, ['Administrative Unit', 'Historical Place'])) {
                    $realEntity = Model_LinkMapper::getLinkedEntity($entity, 'P53');
                }
                if (is_a($entity, 'Model_Entity')) {
                    foreach (Model_LinkMapper::getLinkedEntities($realEntity, $node->propertyToEntity) as $linkedNode) {
                        if ($linkedNode->rootId == $node->id || $linkedNode->id == $node->id) {
                            $nodes[] = $linkedNode;
                        }
                    }
                } else if (is_a($entity, 'Model_Link')) {
                    foreach (Model_LinkPropertyMapper::getLinkedEntities($realEntity, $node->propertyToEntity) as $linkedNode) {
                        if ($linkedNode->rootId == $node->id || $linkedNode->id == $node->id) {
                            $nodes[] = $linkedNode;
                        }
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
        $item = self::getHierarchyByName($rootName);
        $data = "{'data':[" . self::walkTree($item, $selectedIds) . "]}";
        return $data;
    }

    private static function walkTree(Model_Node $item, $selectedIds) {
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
            if (\Craws\FilterInput::filter($node->name, 'node') == \Craws\FilterInput::filter($rootName, 'node')) {
                $rootNode = $node;
                break;
            }
        }
        $options = self::getSuperCandidates($rootNode, 0, -1);
        unset($options[$rootNode->id]); // remove root node
        return $options;
    }

    public static function getHierarchyByName($rootName) {
        foreach (Zend_Registry::get('nodes') as $node) {
            if (\Craws\FilterInput::filter($node->name, 'node') == \Craws\FilterInput::filter($rootName, 'node')) {
                return $node;
            }
        }
    }

    public static function insertHierarchy(Zend_Form $form, Model_Entity $hierarchy) {
        $sql = "INSERT INTO web.hierarchy (id, name, multiple, extendable) VALUES (:id, :name, :multiple, 1)";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':id', $hierarchy->id);
        $statement->bindValue(':name', $hierarchy->name);
        $statement->bindValue(':multiple', $form->getValue('multiple'));
        $statement->execute();
        if ($form->getValue('forms')) {
            foreach ($form->getValue('forms') as $formId) {
                $values[] = '(' . $hierarchy->id . ',' . (int) $formId . ')';
            }
            $sqlForms = "INSERT INTO web.hierarchy_form (hierarchy_id, form_id) VALUES " . implode(',', $values) ;
            $statementForms = Zend_Db_Table::getDefaultAdapter()->prepare($sqlForms . ';');
            $statementForms->execute();
        }
    }

    public static function updateHierarchy(Zend_Form $form, Model_Node $hierarchy) {
        $sql = "UPDATE web.hierarchy SET (name, multiple) = (:name, :multiple) WHERE id = :id";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':id', $hierarchy->id);
        $statement->bindValue(':name', $hierarchy->name);
        $statement->bindValue(':multiple', $form->getValue('multiple'));
        $statement->execute();
        if ($form->getValue('forms')) {
            foreach ($form->getValue('forms') as $formId) {
                $values[] = '(' . $hierarchy->id . ',' . (int) $formId . ')';
            }
            $sqlForms = "INSERT INTO web.hierarchy_form (hierarchy_id, form_id) VALUES " . implode(',', $values) ;
            $statementForms = Zend_Db_Table::getDefaultAdapter()->prepare($sqlForms . ';');
            $statementForms->execute();
        }
    }

}
