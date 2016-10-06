<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap {

    private $settings;

    protected function _initApplication() {
        $this->bootstrap('modules');
        $this->bootstrap('database');
        mb_internal_encoding("UTF-8");
        $this->bootstrap('View');
        $view = $this->getResource('view');
        $this->settings = Model_SettingsMapper::getSettings();
        Zend_Registry::set('settings', $this->settings);
        $view->headTitle($this->settings['sitename'])->setSeparator(' - ');
        Zend_Controller_Action_HelperBroker::addHelper(new \Craws\Controller\Helper\Message());
        Zend_Controller_Action_HelperBroker::addPath(APPLICATION_PATH .
            '/modules/default/controllers/helper/', 'Controller_Helper_Log');
    }

    protected function _initPlugin() {
        $this->bootstrap('user');
        if ($this->settings['offline'] || $this->settings['maintenance']) {
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
            // CoverageIgnore because not able to run bootstrap again
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

    protected function _initLanguage() {
        $this->bootstrap('user');
        $user = Zend_Registry::get('user');
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultLocale = Model_LanguageMapper::getById($this->settings['language'])->shortform;
        $translate = $this->getPluginResource("translate")->getTranslate();
        // @codeCoverageIgnoreStart
        // CoverageIgnore because not accessing with a browser
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

    protected function _initMail() {
        if (!$this->setting['mail'] || !$this->settings['mail_transport_type'] == 'smtp') {
            return; // at the moment only smtp is supported
        }
        $config = array(
            'username' => $this->settings['mail_transport_username'],
            'password' => $this->settings['mail_transport_passwort'],
            'ssl' => $this->settings['mail_transport_ssl'],
            'auth' => $this->settings['mail_transport_auth'],
            'port' => $this->settings['mail_transport_port']
        );
        Zend_Mail::setDefaultTransport(new Zend_Mail_Transport_Smtp($this->settings['mail_transport_host'], $config));
        Zend_Mail::setDefaultFrom($this->settings['mail_from_email'], $this->settings['mail_from_name']);
        /*
        mail_recipients_login: mailRecipientsLogin[] = "office@craws.net"
        mail_recipients_feedback: mailRecipientsFeedback[] = "office@craws.net" ; first entry is shown on feedback site

        resources.mail.transport.ssl = tls
        resources.mail.transport.type = smtp
        resources.mail.transport.host = "mail.craws.net"
        resources.mail.transport.username = "office@craws.net"
        resources.mail.transport.password = CHANGEME
        resources.mail.transport.auth = plain
        resources.mail.defaultFrom.email = "office@craws.net"
        resources.mail.defaultFrom.name = "craws.net"
        */
    }

    protected function _initModel() {
        Zend_Registry::set('classes', Model_ClassMapper::getAll());
        Zend_Registry::set('properties', Model_PropertyMapper::getAll());
        Zend_Registry::set('rootEvent', Model_EntityMapper::getRootEvent());
        Model_NodeMapper::registerHierarchies();
        // @codeCoverageIgnoreStart
        // CoverageIgnore because not testing explicit without nodes
        if (count(Zend_Registry::get('nodes')) < 1) {
            echo ('<p class="error">Warning: nodes are missing (import data_node.sql)</p>');
        }
        // @codeCoverageIgnoreEnd
    }

}
