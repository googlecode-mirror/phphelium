<?php namespace Helium;

/*
 * Validate.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle all data validation
 */

class Validate {
    function __construct() {}

    /**
     *
     * function: checkAll
     * Used for running validation rules
     * @access public
     * @param array $data
     * @param array $rules
     * @return boolean
     */
    public function checkAll($data,$rules) {
        foreach($rules as $rule => $parts) {
            $req = $parts[1];
            if (is_array($req)) {
                if ($data[key($req)] == current($req)) $parts[1] = true;
                else continue;
            }

            $value = str_replace(' ','',$data[$rule]);

            if ($parts[1] == true && $value == '') return false;
            elseif ($parts[1] == false && $value == '') return true;
            else {
                switch($parts[0]) {
                    case 'filled':
                        if (str_replace(' ','',$value) == '') return false;

                    break;

                    case 'date':
                        if ($value == '') return false;
                        elseif (!preg_match('/^(\d{4})-(\d{2})-(\d{2})$/',$value)) return false;

                    break;

                    case 'email':
                        if ($value == '') return false;
                        elseif (!preg_match('/^[a-z0-9_\-]+(\.[_a-z0-9\-]+)*@([_a-z0-9\-]+\.)+([a-z]{2}|aero|arpa|biz|com|coop|edu|gov|info|int|jobs|mil|museum|name|nato|net|org|pro|travel)$/i',$value)) return false;

                    break;

                    case 'phone':
                        if ($value == '') return false;
                        elseif (!preg_match('/^\(?(\d{3})\)?[- ]?(\d{3})[- ]?(\d{4})$/',$value)) return false;

                    break;

                    default:
                        if (!preg_match($rule,$value)) return false;
                }
            }
        }

        return true;
    }
}