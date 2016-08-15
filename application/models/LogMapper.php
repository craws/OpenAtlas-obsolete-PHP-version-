<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_LogMapper extends Model_AbstractMapper {

    public static $logLevels = [
        0 => 'emergency',
        1 => 'alert',
        2 => 'critical',
        3 => 'error',
        4 => 'warn',
        5 => 'notice',
        6 => 'info',
        7 => 'debug'
    ];
    private static $sqlSelect = 'SELECT id, priority, type, message, user_id, ip, agent, created FROM log.log';

    public static function getById($id) {
        $row = parent::getRowById(self::$sqlSelect . ' WHERE id = :id', $id);
        return self::populate($row);
    }

    public static function delete(Model_Log $log) {
        $sql = 'DELETE FROM log.log WHERE id = :id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':id', $log->id);
        $statement->execute();
    }

    public static function getLogs($params) {
        $objects = [];
        $sql = self::$sqlSelect . ' WHERE priority <= :priority';
        if ($params['user_id']) {
            $sql .= ' AND user_id = :user_id';
        }
        $sql .= ' ORDER BY created DESC LIMIT :limit;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':priority', $params['priority']);
        if ($params['user_id']) {
            $statement->bindValue(':user_id', $params['user_id']);
        }
        if ($params['limit']) {
            $statement->bindValue(':limit', $params['limit']);
        } else {
            $statement->bindValue(':limit', null);
        }
        $statement->execute();
        foreach ($statement->fetchAll() as $row) {
            $objects[] = self::populate($row);
        }
        return $objects;
    }

    private static function populate(array $row) {
        $object = new Model_Log();
        $object->id = $row['id'];
        $object->priority = $row['priority'];
        $object->type = $row['type'];
        $object->message = $row['message'];
        $object->userId = $row['user_id'];
        $object->ip = $row['ip'];
        $object->agent = $row['agent'];
        $object->created = parent::toZendDate($row['created']);
        $sql = 'SELECT key, value FROM log.detail WHERE log_id = :log_id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':log_id', $object->id);
        $statement->execute();
        $params = [];
        foreach ($statement->fetchAll() as $row) {
            if (!in_array($row['key'], ['layoutFullContent', 'layoutContent'])) {
                $params[$row['key']] = $row['value'];
            }
        }
        $object->params = $params;
        return $object;
    }

    public static function deleteAll() {
        $sql = 'TRUNCATE TABLE log.log RESTART IDENTITY CASCADE;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->execute();
    }

    public static function log($priorityName, $type, $message = '') {
        $priority = array_search($priorityName, self::$logLevels);
        if ($priority === false || $priority > Model_SettingsMapper::getSetting('general', 'log_level')) {
            return;
        }
        $ip = (filter_input(INPUT_SERVER, 'REMOTE_ADDR')) ? filter_input(INPUT_SERVER, 'REMOTE_ADDR') : '';
        $agent = (filter_input(INPUT_SERVER, 'HTTP_USER_AGENT')) ? filter_input(INPUT_SERVER, 'HTTP_USER_AGENT') : '';
        $sql = 'INSERT INTO log.log (priority, type, message, user_id, ip, agent)
            VALUES (:priority, :type, :message, :user_id, :ip, :agent) RETURNING id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':priority', $priority);
        $statement->bindValue(':type', $type);
        $statement->bindValue(':message', $message);
        $statement->bindValue(':user_id', Zend_Registry::get('user')->id);
        $statement->bindValue(':ip', $ip);
        $statement->bindValue(':agent', $agent);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $logId = $result['id'];
        $frontController = Zend_Controller_Front::getInstance();
        if ($frontController->getRequest() == null || !is_array($frontController->getRequest()->getParams())) {
            return $logId;
        }
        self::logDetails($logId);
        return $logId;
    }

    private static function logDetails($logId) {
        $logDetails = [];
        foreach (Zend_Controller_Front::getInstance()->getRequest()->getParams() as $key => $value) {
            if (is_a($value, 'arrayObject')) {
                // @codeCoverageIgnoreStart
                foreach ($value as $objectKey => $objectValue) {
                    if (is_a($objectValue, 'Exception')) {
                        $logDetails[$objectKey] = $objectValue->getMessage();
                    } else if (is_string($objectValue)) {
                        $logDetails[$objectKey] = $objectValue;
                    } else {
                        $logDetails['unknown param'] = $objectKey;
                    }
                }
            } else {
                // @codeCoverageIgnoreEnd
                if (!in_array($key, ['username', 'password'])) {
                    $logDetails[$key] = $value;
                }
            }
        }
        foreach ($logDetails as $key => $value) {
            $sql = 'INSERT INTO log.detail (log_id, key, value) VALUES (:log_id, :key, :value);';
            $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
            $statement->bindValue(':log_id', $logId);
            $statement->bindValue(':key', $key);
            if (is_string($value)) {
                $statement->bindValue(':value', $value);
            } else {
                $statement->bindValue(':value', 'value was no string');
            }
            $statement->execute();
        }
    }

}
