<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_Form_User extends Craws\Form\Table {

    public function init() {
        $this->setAction($this->getView()->url());
        $this->setName('userForm')->setMethod('post');
        $this->addElement('checkbox', 'active', [
            'label' => $this->getView()->ucstring('active'),
            'checkedValue' => 1,
            'uncheckedValue' => 0,
            'value' => 1,
        ]);
        $this->addElement('text', 'username', [
            'label' => $this->getView()->ucstring('username'),
            'validators' => [['StringLength', false, [1, 32]]],
            'required' => true,
            'class' => 'required',
        ]);
        $groupElement = $this->createElement('select', 'group', ['required' => true, 'class' => 'required']);
        $groupElement->setLabel($this->getView()->ucstring('group'));
        $groupElement->addMultiOption('', html_entity_decode('&nbsp;'));
        $groups = Model_GroupMapper::getAll();
        foreach ($groups as $group) {
            // @codeCoverageIgnoreStart
            // Ignore coverage because testing as admin
            if ($group->name == 'admin' && Zend_Registry::get('user')->group != 'admin') {
                continue;
            }
            // @codeCoverageIgnoreEnd
            $groupElement->addMultiOption($group->id, $group->name);
        }
        $this->addElement($groupElement);
        $this->addElement('password', 'password', [
            'label' => $this->getView()->ucstring('password'),
            'validators' => [['StringLength', false, [8, 256]]],
            'required' => true,
            'class' => 'required',
        ]);
        $this->addElement('password', 'passwordRetype', [
            'label' => $this->getView()->ucstring('password_retype'),
            'validators' => [['StringLength', false, [8, 256]]],
            'required' => true,
            'class' => 'required',
        ]);
        $this->addElement('text', 'realName', [
            'label' => $this->getView()->ucstring('name'),
            'validators' => [['StringLength', false, [1, 64]]],
        ]);
        $this->addElement('text', 'email', [
            'label' => $this->getView()->ucstring('email'),
            'filters' => ['StringToLower'],
            'validators' => [['EmailAddress']],
        ]);
        $this->addElement('textarea', 'info', ['label' => $this->getView()->ucstring('info'), 'rows' => 4, 'cols' => 50]);
        $this->addElement('checkbox', 'registrationMail', [
            'label' => $this->getView()->ucstring('send_registration_mail'),
            'checkedValue' => 1,
            'uncheckedValue' => 0
        ]);
        $this->addElement('button', 'formSubmit', ['label' => $this->getView()->ucstring('insert'), 'type' => 'submit']);
        $this->setElementFilters(['StringTrim']);
    }

    public function prepareUpdate(Model_User $user) {
        $this->removeElement('password');
        $this->removeElement('passwordRetype');
        if (Zend_Registry::get('user')->id == $user->id) {
            $this->removeElement('active');
        }
        $this->getElement('formSubmit')->setLabel($this->getView()->ucstring('save'));
    }

}
