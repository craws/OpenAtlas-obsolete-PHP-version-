<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_Description extends Zend_View_Helper_Abstract {

    public function description(\Model_Entity $entity) {
        if ($entity->description) {
            $html = '<div class="description">
                    <p class="descriptionTitle">' . $this->view->ucstring('description') . '</p>
                    <p>' . nl2br($entity->description) . '</p>
                    </div>';
            return $html;
        }
        return;
    }

}
