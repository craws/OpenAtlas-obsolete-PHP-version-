<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_OverviewController extends Zend_Controller_Action {


    public function feedbackAction() {
        $form = new Admin_Form_Feedback();
        $settings = Model_SettingsMapper::getSettings();
        $receivers = explode(',', $settings['mail_recipients_feedback']);
        $this->view->form = $form;
        $this->view->feedbackEmail = $receivers[0];
        // @codeCoverageIgnoreStart
        // Ignore coverage because no mail in testing
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost()) && $settings['mail']) {
            $mail = new Zend_Mail('utf-8');
            foreach($receivers as $receiver) {
                $mail->addTo($receiver);
            }
            $mail->setSubject($form->getValue('subject') . ' from ' . $settings['sitename']);
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
        $count['source'] = count(Model_EntityMapper::getByCodes('Source'));
        $count['event'] = Model_EntityMapper::countByCodes('Event');
        $count['actor'] = Model_EntityMapper::countByCodes('Actor');
        $count['place'] = Model_EntityMapper::countByCodes('PhysicalObject');
        $count['reference'] = Model_EntityMapper::countByCodes('Reference');
        $this->view->count = $count;
        $this->view->latestEntries = Model_EntityMapper::getLatest(8);
        $bookmarks = [];
        foreach (Zend_Registry::get('user')->bookmarks as $id) {
            $bookmarks[] = Model_EntityMapper::getById($id);
        }
        $this->view->bookmarks = $bookmarks;
    }

    public function networkAction() {
        $namespace = new Zend_Session_Namespace('Default');
        if (!$namespace->network) {
            $namespace->network['classes'] = [
                'E21' => ['active' => true,  'color' => '#34B522'], // Person
                'E7'  => ['active' => true,  'color' => '#E54A2A'], // Activity
                'E31' => ['active' => false, 'color' => '#FFA500'], // Document
                'E33' => ['active' => false, 'color' => '#FFA500'], // Linguistic Object
                'E40' => ['active' => true,  'color' => '#34623C'], // Legal Body
                'E74' => ['active' => true,  'color' => '#34623C'], // Group
                'E53' => ['active' => false, 'color' => '#00FF00'], // Places
                'E18' => ['active' => false, 'color' => '#FF0000'], // Physical Object
                'E8'  => ['active' => true,  'color' => '#E54A2A'], // Aquesition
                'E12' => ['active' => true,  'color' => '#E54A2A'], // Production
                'E6'  => ['active' => true,  'color' => '#E54A2A'], // Destruction
                'E84' => ['active' => false, 'color' => '#EE82EE'], // Information Carrier
            ];
            $namespace->network['properties'] = [
                'P107' => ['active' => true],  // has current or former member
                'P11'  => ['active' => true],  // had participant
                'P14'  => ['active' => true],  // carried out by
                'P7'   => ['active' => true], // took place at
                'P74'  => ['active' => true], // has current or former residence
                'P67'  => ['active' => true], // refers to
                'OA7'  => ['active' => true],  // has relationship to
                'OA8'  => ['active' => true], // appears for the first time in
                'OA9'  => ['active' => true], // appears for the last time in
            ];
            $namespace->network['options'] = [
                'show orphans' => false,
                'width'  => 1200,
                'height'  => 600,
                'charge' => -800,
                'linkDistance' => 80
            ];
        }
        if ($this->getRequest()->isPost()) {
            foreach ($namespace->network['classes'] as $code => $params) {
                $namespace->network['classes'][$code]['active'] = false;
                $namespace->network['classes'][$code]['color'] = $this->_getParam($code . '_color');
                if ($this->_getParam($code)) {
                    $namespace->network['classes'][$code]['active'] = true;
                }
            }
            foreach ($namespace->network['properties'] as $code => $params) {
                $namespace->network['properties'][$code]['active'] = false;
                if ($this->_getParam($code)) {
                    $namespace->network['properties'][$code]['active'] = true;
                }
            }
            foreach ($namespace->network['options'] as $option => $value) {
                if ($option == 'show orphans') {
                    $namespace->network['options']['show orphans'] = false;
                    if ($this->_getParam('show-orphans')) {
                        $namespace->network['options']['show orphans'] = true;
                    }
                    continue;
                }
                $namespace->network['options'][$option] = $this->_getParam($option);
            }
        }
        $this->view->networkData = Model_Network::getData();
    }
}
