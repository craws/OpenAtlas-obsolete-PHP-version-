<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Admin_ContentController extends Zend_Controller_Action {

    public function indexAction() {
        $this->view->contents = Model_ContentMapper::getAll();
    }

    public function updateAction() {
        $content = Model_ContentMapper::getById($this->_getParam('id'));
        $form = new Admin_Form_Content();
        foreach ($content->texts as $languageShortform => $values) {
            $form->getSubForm($languageShortform)->populate($values);
        }
        if ($this->getRequest()->isPost() && $form->isValid($this->getRequest()->getPost())) {
            $texts = [];
            foreach (Model_LanguageMapper::getAll() as $language) {
                $texts[$language->shortform] = $form->getValue($language->shortform);
            }
            $content->texts = $texts;
            $content->update();
            $this->_helper->message('info_update');
            return $this->_helper->redirector->gotoUrl('/admin/content/view/id/' . $content->id);
        }
        $this->view->form = $form;
        $this->view->content = $content;
        $this->renderScript('content/update.phtml');
    }

    public function viewAction() {
        $this->view->content = Model_ContentMapper::getById($this->_getParam('id'));
    }

}
