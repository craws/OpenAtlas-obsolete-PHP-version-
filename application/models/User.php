<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Model_User extends Model_AbstractObject {

    public $active;
    public $username;
    public $password;
    public $loginLastSuccess;
    public $loginLastFailure;
    public $loginFailedCount;
    public $realName;
    public $email;
    public $info;
    public $settings = [];
    public $bookmarks = [];
    public $passwordResetCode;
    public $passwordResetDate;
    public $unsubscribeCode;
    public $group = 'guest';

    public function getSetting($name) {
        $settings = $this->settings;
        if (isset($settings[$name]) && $settings[$name]) {
            return $settings[$name];
        }
        return false;
    }

    public static function loginAttemptsExceeded(Model_User $user) {
        if (!$user->loginLastFailure) {
            return false;
        }
        $lastFailureDate = $user->loginLastFailure;
        $lastFailureDate->addMinute(Model_SettingsMapper::getSetting('failed_login_forget_minutes'));
        if ($lastFailureDate->isLater(new Zend_Date()) &&
          $user->loginFailedCount >= Model_SettingsMapper::getSetting('failed_login_tries')) {
            return true;
        }
        return false;
    }

    public static function randomPassword($length = 8) {
        $pass = [];
        $characters = "abcdefghijkmnpqrstuwxyzABCDEFGHJKLMNPQRSTUWXYZ23456789";
        $charactersLength = strlen($characters) - 1;
        for ($i = 0; $i < $length; $i++) {
            $pass[] = $characters[rand(0, $charactersLength)];
        }
        return implode($pass);
    }

    public static function hasher($info, $encdata = false) {
        $strength = "08";
        if ($encdata) { /* if encrypted data is passed, check it against $info */
            if (substr($encdata, 0, 60) == crypt($info, "$2a$" . $strength . "$" . substr($encdata, 60))) {
                return true;
            } else {
                return false;
            }
        } else {
            $salt = ""; /* make a salt, hash it with input and add salt to end */
            for ($i = 0; $i < 22; $i++) {
                $salt .= substr("./ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789", mt_rand(0, 63), 1);
            }
            return crypt($info, "$2a$" . $strength . "$" . $salt) . $salt;
        }
    }

}
