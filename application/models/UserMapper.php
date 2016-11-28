<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_UserMapper extends Model_AbstractMapper {

    private static $sqlSelect = '
        SELECT u.id, u.username, u.password, u.active, u.real_name, u.info, u.created, u.modified,
            u.login_last_success, u.login_last_failure, u.login_failed_count, u.password_reset_code,
            u.password_reset_date, u.email, r.name as group_name
        FROM web."user" u
        LEFT JOIN web.group r ON u.group_id = r.id ';

    public static function getById($id, $failureException = true) {
        $row = parent::getRowById(self::$sqlSelect . ' WHERE u.id = :id;', $id, $failureException);
        if ($row) {
            return self::populate($row);
        }
    }

    public static function getAll() {
        $users = [];
        $sql = self::$sqlSelect . ' ORDER BY u.active DESC, u.username;';
        foreach (parent::getAllRows($sql) as $row) {
            $users[] = self::populate($row);
        }
        return $users;
    }

    public static function getByUsername($username) {
        $sql = 'SELECT id FROM web."user" WHERE LOWER(username) = :username;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':username', mb_strtolower($username));
        $statement->execute();
        $row = $statement->fetch();
        if ($row) {
            return self::getById($row['id']);
        }
        return false;
    }

    public static function getBookmarks($userId) {
        $sql = 'SELECT entity_id FROM web.user_bookmarks WHERE user_id = :user_id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':user_id', mb_strtolower($userId));
        $statement->execute();
        $bookmarks = [];
        foreach ($statement->fetchall() as $row) {
            $bookmarks[$row['entity_id']] = $row['entity_id'];
        }
        return $bookmarks;
    }

    public static function bookmark($entityId) {
        $user = Zend_Registry::get('user');
        $sql = 'INSERT INTO web.user_bookmarks (user_id, entity_id) VALUES (:user_id, :entity_id);';
        $label = 'bookmark_remove';
        if (isset($user->bookmarks[$entityId])) {
            $sql = 'DELETE FROM web.user_bookmarks WHERE user_id = :user_id AND entity_id = :entity_id;';
            $label = 'bookmark';
        }
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':user_id', $user->id);
        $statement->bindValue(':entity_id', $entityId);
        $statement->execute();
        return $label;
    }

    // @codeCoverageIgnoreStart
    // Ignore coverage because no mail in testing
    public static function getByResetCode($code) {
        if (!$code) {
            return false;
        }
        $sql = 'SELECT id FROM web."user" WHERE password_reset_code = :password_reset_code;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':password_reset_code', $code);
        $statement->execute();
        $row = $statement->fetch();
        if (!$row) {
            return false;
        }
        return self::getById($row['id']);
    }

    public static function getByUnsubscribeCode($code) {
        if (!$code) {
            return false;
        }
        $sql = 'SELECT id FROM web."user" WHERE unsubscribe_code = :unsubscribe_code;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':unsubscribe_code', $code);
        $statement->execute();
        $row = $statement->fetch();
        if (!$row) {
            return false;
        }
        return self::getById($row['id']);
    }

    // @codeCoverageIgnoreEnd

    public static function getByEmail($email) {
        $sql = 'SELECT id FROM web."user" WHERE LOWER(email) = :email;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':email', $email);
        $statement->execute();
        $row = $statement->fetch();
        if (!$row) {
            return false;
        }
        return self::getById($row['id']);
    }

    private static function populate(array $row) {
        $user = new Model_User();
        $user->id = $row['id'];
        $user->active = $row['active'];
        $user->username = $row['username'];
        $user->password = $row['password'];
        $user->realName = $row['real_name'];
        $user->email = $row['email'];
        $user->info = $row['info'];
        $user->loginLastSuccess = parent::toZendDate($row['login_last_success']);
        $user->loginLastFailure = parent::toZendDate($row['login_last_failure']);
        $user->loginFailedCount = $row['login_failed_count'];
        $user->passwordResetCode = $row['password_reset_code'];
        $user->passwordResetDate = parent::toZendDate($row['password_reset_date']);
        $user->created = parent::toZendDate($row['created']);
        $user->modified = parent::toZendDate($row['modified']);
        $user->settings = self::getSettings($user);
        $user->group = $row['group_name'];
        return $user;
    }

    public static function insert(Model_User $user) {
        $sql = 'INSERT INTO web."user" (username, password, active, real_name, info, email, group_id)
            VALUES (:username, :password, :active, :real_name, :info, :email, :group_id) RETURNING id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':username', $user->username);
        $statement->bindValue(':password', $user->password);
        $statement->bindValue(':real_name', $user->realName);
        $statement->bindValue(':info', $user->info);
        $statement->bindValue(':email', $user->email ? $user->email : null);
        $statement->bindValue(':active', $user->active);
        $statement->bindValue(':group_id', Model_GroupMapper::getByName($user->group)->id);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        $user->id = $result['id'];
        return $user->id;
    }

    public static function update(Model_User $user) {
        $sql = 'UPDATE web."user" SET
            (username, active, login_last_success, login_last_failure, login_failed_count, real_name, info,
            email, password_reset_code, password_reset_date, group_id, unsubscribe_code) =
            (:username, :active, :login_last_success, :login_last_failure, :login_failed_count, :real_name, :info,
            :email, :password_reset_code, :password_reset_date, :group_id, :unsubscribe_code)
            WHERE id = :id RETURNING id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':id', $user->id);
        $statement->bindValue(':username', $user->username);
        $statement->bindValue(':active', $user->active);
        $statement->bindValue(':real_name', $user->realName);
        $statement->bindValue(':info', $user->info);
        $statement->bindValue(':email', $user->email ? $user->email : null);
        $statement->bindValue(':group_id', Model_GroupMapper::getByName($user->group)->id);
        $statement->bindValue(':login_failed_count', $user->loginFailedCount);
        $statement->bindValue(':password_reset_code', $user->passwordResetCode);
        $statement->bindValue(':password_reset_date', parent::toDbDate($user->passwordResetDate));
        $statement->bindValue(':login_last_success', parent::toDbDate($user->loginLastSuccess));
        $statement->bindValue(':login_last_failure', parent::toDbDate($user->loginLastFailure));
        $statement->bindValue(':unsubscribe_code', $user->unsubscribeCode);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result['id'];
    }

    public static function updatePassword(Model_User $user) {
        $sql = 'UPDATE web."user" SET password = :password WHERE id = :id RETURNING id;';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue(':id', $user->id);
        $statement->bindValue(':password', $user->password);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);
        return $result['id'];
    }

    public static function delete(Model_User $user) {
        parent::deleteAbstract('web.user', $user->id);
    }

    private static function getSettings(Model_User $user) {
        $sql = 'SELECT "name", value FROM web.user_settings WHERE user_id = :user_id;';
        $statement = Zend_DB_Table::getDefaultAdapter()->prepare($sql);
        $statement->bindValue('user_id', $user->id);
        $statement->execute();
        $settings = [];
        foreach ($statement->fetchall() as $row) {
            $settings[$row['name']] = $row['value'];
        }
        return $settings;
    }

    public static function updateSettings(Model_User $user) {
        $oldSettings = self::getSettings($user);
        $newSettings = $user->settings;
        $sql = 'SELECT FROM web.user_settings (user_id, "name", "value") VALUES (:user_id, :name, :value);';
        $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
        foreach ($newSettings as $name => $value) {
            if (isset($oldSettings[$name])) {
                $sql = 'UPDATE web.user_settings SET "value" = :value WHERE user_id = :user_id AND "name" = :name;';
                $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
            } else {
                $sql = 'INSERT INTO web.user_settings (user_id, "name", "value") VALUES (:user_id, :name, :value);';
                $statement = Zend_Db_Table::getDefaultAdapter()->prepare($sql);
            }
            if (!$value) {
                $value = 0;
            }
            $statement->bindValue('user_id', $user->id);
            $statement->bindValue('name', $name);
            $statement->bindValue('value', $value);
            $statement->execute();
            if ($name == "language") {
                $language = Model_LanguageMapper::getById($value);
                $defaultNamespace = new Zend_Session_Namespace('Default');
                $defaultNamespace->language = $language->shortform;
            }
        }
    }

}
