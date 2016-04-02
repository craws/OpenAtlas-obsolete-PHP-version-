<?php

/* Copyright 2016 by Alexander Watzinger and others. Please see the file README.md for licensing information */

class Craws_View_Helper_FormatTableData extends Zend_View_Helper_Abstract {

    public function formatTableData($value) {
        $valueStyle = '';
        if (is_numeric($value)) {
            $valueStyle = ' style="text-align:right"';
        }
        if (is_a($value, 'Zend_Date')) {
            $value = $this->view->printDate($value);
        }
        return '<td class="value"' . $valueStyle . '>' . $value . '</td>';
    }

}
