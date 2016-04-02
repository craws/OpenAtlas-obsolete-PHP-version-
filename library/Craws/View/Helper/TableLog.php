<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_TableLog extends Zend_View_Helper_Abstract {

    public function tablelog($table, $tableName, $id) {
        if (Zend_Registry::get('user')->getSetting('layout') == 'advanced') {
            $log = Model_UserLogMapper::getLogForView($tableName, $id);
            $entity = Model_EntityMapper::getById($id);
            $table['data'][_('class')] = $this->view->link($entity->getClass());
            if ($log['created']) {
                $table['data'][_('created')] = $this->view->printDate($log['created']) . ' ' . $log['creator_name'];
            }
            if ($log['modified']) {
                $table['data']['modified'] = $this->view->printDate($log['modified']) . ' ' . $log['modifier_name'];
            }
        }
        return $table;
    }

}
