<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_Link extends Zend_View_Helper_Abstract {

    public function link($object, $action = null, $label = null, $relatedObject = null, $tab = '') {
        if (is_object($object)) {
            return self::printObjectLink($object, $action, $label, $tab);
        }
        if (!$action) {
            if (!$label) {
                $label = $object;
            }
            return '<a href="/admin/' . $object . '">' . $this->view->ucstring($label) . '</a>';
        }
        $class = Model_ClassMapper::getByCode($object);
        $caption = '+ ' . $class->nameTranslated;
        if (in_array($object, Zend_Registry::get('config')->get('codeEvent')->toArray())) {
            $controller = 'event';
        } else if (in_array($object, Zend_Registry::get('config')->get('codeActor')->toArray())) {
            $controller = 'actor';
        } else if (in_array($object, Zend_Registry::get('config')->get('codeSource')->toArray())) {
            $controller = 'source';
            $caption = '+ ' . $this->view->ucstring('source');
        } else if (in_array($object, Zend_Registry::get('config')->get('codePhysicalObject')->toArray())) {
            $controller = 'place';
            $caption = '+ ' . $this->view->ucstring('place');
        }
        $relatedParam = '';
        if ($relatedObject) {
            if (in_array($relatedObject->class->code, Zend_Registry::get('config')->get('codeEvent')->toArray())) {
                $relatedParam = '/origin/event/eventId';
            } else if (in_array($relatedObject->class->code, Zend_Registry::get('config')->get('codeActor')->toArray())) {
                $relatedParam = '/origin/actor/actorId';
            } else if (in_array($relatedObject->class->code, Zend_Registry::get('config')->get('codeSource')->toArray())) {
                $relatedParam = '/origin/source/sourceId';
            } else if (in_array($relatedObject->class->code, Zend_Registry::get('config')->get('codePhysicalObject')->toArray())) {
                $relatedParam = '/origin/source/objectId';
            }
            $relatedParam .= '/' . $relatedObject->id;
        }
        $link = '<a href="' . '/admin/' . $controller . '/insert' . '/code/' . $object . $relatedParam . '">' . $caption . '</a>';
        return $link;
    }

    private function printObjectLink($object, $action, $label, $tab) {
        if (!$action) {
            $action = 'view';
        }
        if ($tab) {
            $tab = '/#tab' . $tab;
        }
        $onclick = '';
        if ($label) {
            $label = $this->view->ucstring($label);
        } else if ($action == 'update') {
            $label = $this->view->ucstring('edit');
        } else if ($action == 'delete') {
            $label = $this->view->ucstring('delete');
            $onclick = " onclick=\"return confirm('" .
                $this->view->ucstring($this->view->translate('confirm_delete', $object->name)) . "')\" ";
        } else if (isset($object->nameTranslated) && $object->nameTranslated) {
            $label = $object->nameTranslated;
        } else {
            $label = $object->name;
        }
        $link = '<a href="/admin/' . $this->getController($object) . '/' . $action . '/id/' . $object->id . $tab . '"' .
            $onclick . '>' . $label . '</a>';
        return $link;
    }

    private function getController($object) {
        if (is_a($object, 'Model_Class')) {
            return 'class';
        }
        if (is_a($object, 'Model_Property')) {
            return 'property';
        }
        $array = Zend_Registry::get('config')->get('codeView')->toArray();
        // @codeCoverageIgnoreStart
        if (!$array[$object->class->code]) {
            return '"</a><span style="color:red;">undefined code ' . $object->class->code . ' for link <';
        }
        // @codeCoverageIgnoreEnd
        return $array[$object->class->code];
    }

}
