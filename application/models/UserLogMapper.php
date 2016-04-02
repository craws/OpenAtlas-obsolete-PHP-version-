<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_UserLogMapper extends Model_AbstractMapper {

    public static function insert($tableName, $tableId, $action) {
        $sql = 'INSERT INTO web.user_log (user_id, table_name, table_id, action) ' .
            'VALUES (:user_id, :table_name, :table_id, :action);';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':user_id', Zend_Registry::get('user')->id);
        $statement->bindValue(':table_name', $tableName);
        $statement->bindValue(':table_id', $tableId);
        $statement->bindValue(':action', $action);
        $statement->execute();
    }

    public static function getLogForView($tableName, $tableId) {
        $log = [
          'creator_id' => null,
          'creator_name' => null,
          'created' => null,
          'modifier_id' => null,
          'modifier_name' => null,
          'modified' => null
        ];
        $sql = "
            SELECT ul.created, ul.user_id, ul.table_name, ul.table_id, u.username
            FROM web.user_log ul
            LEFT OUTER JOIN web.user u ON ul.user_id = u.id
            WHERE ul.table_name = :table_name AND ul.table_id = :table_id AND ul.action = :action
            ORDER BY ul.created DESC LIMIT 1;";
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':table_name', $tableName);
        $statement->bindValue(':table_id', $tableId);
        $statement->bindValue(':action', 'insert');
        $statement->execute();
        $row = $statement->fetch();
        if ($row) {
            $log['creator_id'] = $row['user_id'];
            $log['creator_name'] = $row['username'];
            $log['created'] = parent::toZendDate($row['created']);
        }
        $statementModified = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statementModified->bindValue(':table_name', $tableName);
        $statementModified->bindValue(':table_id', $tableId);
        $statementModified->bindValue(':action', 'update');
        $statementModified->execute();
        $rowModified = $statementModified->fetch();
        if ($rowModified) {
            $log['modifier_id'] = $rowModified['user_id'];
            $log['modifier_name'] = $rowModified['username'];
            $log['modified'] = parent::toZendDate($rowModified['created']);
        }
        return $log;
    }

}
