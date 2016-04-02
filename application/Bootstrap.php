<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    protected function _initApplication() {
        $this->bootstrap('modules');
        $this->bootstrap('database');
        mb_internal_encoding("UTF-8");
        $this->bootstrap('View');
        $view = $this->getResource('view');
        Zend_Registry::set('settings', Model_SettingsMapper::getSettings());
        $view->headTitle(Model_SettingsMapper::getSetting('general', 'sitename'))->setSeparator(' - ');
        Zend_Controller_Action_HelperBroker::addHelper(new \Craws\Controller\Helper\Message());
        Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH .
            '/modules/default/controllers/helper/', 'Controller_Helper_Log');
    }

    protected function _initPlugin() {
        $this->bootstrap('user');
        if (Model_SettingsMapper::getSetting('general', 'offline') ||
            Model_SettingsMapper::getSetting('general', 'maintenance')) {
            $front = $this->getResource('frontController');
            $plugin = new \Craws\Plugin\Offline();
            $front->registerPlugin($plugin);
            $front->throwExceptions(true);
        }
    }

    protected function _initConfig() {
        $config = new Zend_Config($this->getOptions());
        Zend_Registry::set('config', $config);
        return $config;
    }

    protected function _initUser() {
        $this->bootstrap('modules');
        $this->bootstrap('database');
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
            // @codeCoverageIgnoreStart
            $user = Model_UserMapper::getById($auth->getIdentity()->id);
            $user->bookmarks = Model_UserMapper::getBookmarks($user->id);
            Zend_Registry::set('user', $user);
        } else {
            // @codeCoverageIgnoreEnd
            Zend_Registry::set('user', new Model_User());
        }
    }

    protected function _initDatabase() {
        $this->bootstrap('multidb');
        $resource = $this->getPluginResource('multidb');
        $databaseAdapter = $resource->getDb();
        return $databaseAdapter;
    }

    protected function _initModuleSettings() {
        $settings = Model_SettingsMapper::getSettings();
        Zend_Registry::set('moduleSettings', $settings['module']); /* needed for acl */
    }

    protected function _initLanguage() {
        $this->bootstrap('user');
        $user = Zend_Registry::get('user');
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultLocale = Model_LanguageMapper::getById(Model_SettingsMapper::getSetting('general', 'language'))->shortform;
        $translate = $this->getPluginResource("translate")->getTranslate();
        // @codeCoverageIgnoreStart
        if (filter_input(INPUT_GET, 'lang')) {
            $locale = filter_input(INPUT_GET, 'lang');
            if ($user->active) {
                $user->settings['language'] = Model_LanguageMapper::getByShortform($locale)->id;
                Model_UserMapper::updateSettings($user);
            }
        } else if ($user->getSetting('language')) {
            $locale = Model_LanguageMapper::getById($user->getSetting('language'))->shortform;
        } else if (isset($defaultNamespace->language)) {
            $locale = (string) $defaultNamespace->language;
        } else {
            try {
                $locale = (string) new Zend_Locale('browser');
            } catch (Zend_Locale_Exception $e) {
                Model_LogMapper::log('debug', 'i18n', 'Setting local failed: ' . $e);
                $locale = $defaultLocale;
            }
        }
        if (!in_array($locale, $translate->getList())) {
            $locale = $defaultLocale;
        }
        // @codeCoverageIgnoreEnd
        $defaultNamespace->language = $locale;
        $translate->setLocale($locale);
        Zend_Registry::set('Zend_Translate', $translate);
        Zend_Registry::set('Zend_Locale', $locale);
        Zend_Registry::set('Default_Locale', $defaultLocale);
    }

    protected function _initModel() {
        Zend_Registry::set('classes', Model_ClassMapper::getAll());
        Zend_Registry::set('properties', Model_PropertyMapper::getAll());
        Model_NodeMapper::setAll();
        // @codeCoverageIgnoreStart
        if (count(Zend_Registry::get('type')) < 1) {
            echo ('<p class="error">Warning: nodes are missing (import data_node.sql)</p>');
        }
        // @codeCoverageIgnoreEnd
    }

}
