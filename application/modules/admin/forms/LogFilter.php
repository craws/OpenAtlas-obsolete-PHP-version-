<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_LogFilter extends Craws\Form\Table {

    public function init() {
        $this->setName('logFilterForm')->setMethod('post');
        $limit = $this->createElement('select', 'limit');
        $limit->setLabel($this->getView()->ucstring('limit'));
        $limit->addMultiOptions([0 => 'all', 100 => '100', 300 => '300', 500 => '500']);
        $this->addElement($limit);
        $userElement = $this->createElement('select', 'user_id');
        $userElement->setLabel($this->getView()->ucstring('user'));
        $users = Model_UserMapper::getAll();
        $userElement->addMultiOption(0, 'all');
        foreach ($users as $user) {
            $userElement->addMultiOption($user->id, $user->username);
        }
        $this->addElement($userElement);
        $log = $this->createElement('select', 'priority');
        $log->setLabel($this->getView()->ucstring('priority'));
        $log->addMultiOptions([
            0 => 'emergency',
            1 => 'alert',
            2 => 'critical',
            3 => 'error',
            4 => 'warn',
            5 => 'notice',
            6 => 'info',
            7 => 'debug'
        ]);
        $this->addElement($log);
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring('apply'), 'type' => 'submit']);
        $this->setElementFilters(['StringTrim']);
    }

}
