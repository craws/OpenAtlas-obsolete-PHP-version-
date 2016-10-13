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
            $mail->setFrom($settings['mail_from_email'], $settings['mail_from_name']);
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

}
