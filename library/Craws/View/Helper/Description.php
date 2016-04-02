<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_Description extends Zend_View_Helper_Abstract {

    public function description(\Model_Entity $entity) {
        if ($entity->description) {
            $html = '<div class="description">';
            $html .= '<p class="descriptionTitle">' . $this->view->ucstring('description') . '</p>';
            $html .= '<p>' . $entity->description . '</p>';
            $html .= '</div>';
            return $html;
        }
        return;
    }

}
