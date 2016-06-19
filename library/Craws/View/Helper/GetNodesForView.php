<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_GetNodesForView extends Zend_View_Helper_Abstract {

    public function getNodesForView ($entitiy) {
        $nodes = [];
        foreach (Model_LinkMapper::getLinkedEntities($entitiy, 'P2') as $node) {
            if ($node->rootId && !in_array($node->name, ['Source Content'])) {
                $nodes[Model_NodeMapper::getById($node->rootId)->name][] = $node->name;
            }
        }
        ksort($nodes);
        return $nodes;
    }
}
