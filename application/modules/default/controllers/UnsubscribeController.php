<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class UnsubscribeController extends Zend_Controller_Action {

    public function indexAction() {
        $code = $this->_getParam('code');
        $subscriber = Model_UserMapper::getByUnsubscribeCode($code);
        $this->view->subscriber = $subscriber;
        $this->view->code = $code;
        // @codeCoverageIgnoreStart
        if ($this->_getParam('confirm') && $subscriber) {
            $subscriber->unsubscribeCode = Null;
            $subscriber->settings['newsletter'] = 0;
            $subscriber->update();
            Model_UserMapper::updateSettings($subscriber);
            $this->_helper->message($this->view->translate('info_unsubscribed'));
            return $this->_helper->redirector->gotoUrl('/');
        }
        // @codeCoverageIgnoreEnd
    }

}
