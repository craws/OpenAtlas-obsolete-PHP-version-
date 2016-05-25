<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_OverviewController extends Zend_Controller_Action {


    public function changelogAction() {

    }

    public function creditsAction() {

    }

    public function feedbackAction() {
        $form = new Admin_Form_Feedback();
        $receivers = Zend_Registry::get('config')->get('mailRecipientsFeedback')->toArray();
        $this->view->form = $form;
        $this->view->feedbackReceiver = $receivers[0];
        // @codeCoverageIgnoreStart
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) &&
            Model_SettingsMapper::getSetting('module', 'mail')) {
            $mail = new Zend_Mail('utf-8');
            foreach($receivers as $receiver) {
                $mail->addTo($receiver);
            }
            $mail->setSubject($form->getValue('subject') . ' from ' . Model_SettingsMapper::getSetting('general', 'sitename'));
            $user = Zend_Registry::get('user');
            $body = $form->getValue('subject') . ' from ' . $user->username . ' (' . $user->id . ') ' . $user->email .
                ' at ' . $this->getRequest()->getHttpHost() . "\n\n" . $form->getValue('description');
            $mail->setBodyText($body);
            if ($mail->send()) {
                $this->_helper->log('info', 'mail', 'Feedback mail send');
                $this->_helper->message($this->view->translate('info_feedback_thanks'));
                $this->_helper->redirector->gotoUrl('/admin');
            } else {
                $this->_helper->log('error', 'mail', 'Sending feedback mail to ' .
                    Zend_Registry::get('config')->resources->mail->transport->username);
                $this->_helper->message('error_mail_send');
            }
        }
        // @codeCoverageIgnoreEnd
    }

    public function indexAction() {
        $count['source'] = count(Model_EntityMapper::getByCodes('Source', 'Source Content'));
        $count['event'] = Model_EntityMapper::countByCodes('Event');
        $count['actor'] = Model_EntityMapper::countByCodes('Actor');
        $count['place'] = Model_EntityMapper::countByCodes('PhysicalObject');
        $count['reference'] = Model_EntityMapper::countByCodes('Reference');
        $this->view->count = $count;
        $this->view->latestEntries = Model_EntityMapper::getLatest(5);
        $bookmarks = [];
        foreach (Zend_Registry::get('user')->bookmarks as $id) {
            $bookmarks[] = Model_EntityMapper::getById($id);
        }
        $this->view->bookmarks = $bookmarks;
    }

    public function modelAction() {
        $form = new Admin_Form_Test();
        $classes = Zend_Registry::get('classes');
        $properties = Zend_Registry::get('properties');
        $this->view->count = [];
        $this->view->count['classes'] = count($classes);
        $this->view->count['properties'] = count($properties);
        $this->view->form = $form;
        if (!$this->getRequest()->isPost() || !$form->isValid($this->getRequest()->getPost())) {
            return;
        }
        $domain = $classes[$this->_getParam('domain')];
        $range = $classes[$this->_getParam('range')];
        $property = $properties[$this->_getParam('property')];
        $whitelistDomains = Zend_Registry::get('config')->get('linkcheckIgnoreDomains')->toArray();
        $this->view->testResult = [];
        if (!in_array($domain->code, $property->getDomain()->getSubRecursive())) {
            $this->view->testResult['domainError'] = true;
        }
        if (!in_array($range->code, $property->getRange()->getSubRecursive())) {
            $this->view->testResult['rangeError'] = true;
        }
        if (in_array($domain->code, $whitelistDomains)) {
            $this->view->testResult['domainWhitelist'] = true;
        }
        $this->view->testResult['domain'] = $domain;
        $this->view->testResult['property'] = $property;
        $this->view->testResult['range'] = $range;
    }

}
