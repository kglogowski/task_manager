<?php

namespace App\LibBundle;

class Float {
    
    public static function toFloat($str, $precision = 2) {
        $kwota = floatval(str_replace(",", ".", $str));
        $patternFloat = '/^[0-9]+(\.[0-9]{0,2})?$/';
        return preg_match($patternFloat, $kwota) ? $kwota : false;
    }
    
}
