<?php namespace Helium;

/*
 * DateData.php
 * Copyright: Bryan Healey 2010, 2011 (bryan@bryanhealey.com)
 * License: GNU General Public License (v3)
 * Purpose: Handle all date activity
 */

class DateData {
    function __construct() {}

    /**
     *
     * function: dowToText
     * Turns an int representing the day of the week into human readable text
     * @access public
     * @param string $dow
     * @param boolean $lower (optional)
     * @return string
     */
    public function dowToText($dow,$lower=false) {
        $return = false;

        switch($dow) {
            case 0: $return = 'Sunday'; break;
            case 1: $return = 'Monday'; break;
            case 2: $return = 'Tuesday'; break;
            case 3: $return = 'Wednesday'; break;
            case 4: $return = 'Thursday'; break;
            case 5: $return = 'Friday'; break;
            case 6: $return = 'Saturday'; break;
        }

        if ($return && $lower) $return = strtolower($return);
        return $return;
    }

    /**
     *
     * function: textToDOW
     * Turns an string representing the day of the week into into representative
     * @access public
     * @param string $text
     * @return int
     */
    public function textToDOW($text) {
        switch(strtolower($text)) {
            case 'sunday': return 0; break;
            case 'monday': return 1; break;
            case 'tuesday': return 2; break;
            case 'wednesday': return 3; break;
            case 'thursday': return 4; break;
            case 'friday': return 5; break;
            case 'saturday': return 6; break;
        }

        return false;
    }

    /**
     *
     * function: timeBetween
     * Takes two dates and determines the amount of time between them
     * @access public
     * @param DateTime $first
     * @param DateTime $second (optional)
     * @param boolean $readable (optional)
     * @return [boolean,int,string]
     */
    public function timeBetween($first,$second=false,$readable=false) {
        if (!is_numeric($first)) $first = strtotime($first);
        if (empty($second)) $second = time();
        if (!is_numeric($second)) $second = strtotime($second);

        if ($first > $second) return false;
        elseif (!$readable) return $second-$first;
        elseif ($readable) {
            $base = $second-$first;

            $minutes = floor($base/60);
            $seconds = $base-($minutes*60);

            $hours = floor($minutes/60);
            $minutes = $minutes-($hours*60);

            $days = floor($hours/24);
            $hours = $hours-($days*24);

            $readable = '';
            if ($days > 0) $readable .= $days.' days, ';
            if ($hours > 0) $readable .= $hours.' hours, ';
            if ($minutes > 0) $readable .= $minutes.' minutes, ';
            if ($seconds > 0) $readable .= $seconds.' seconds';

            return $readable;
        }
    }

    /**
     *
     * function: readableTimestamp
     * Turns an int timestamp into human readable syntax
     * @access public
     * @param int $input
     * @return string
     */
    public function readableTimestamp($input) {
        $diff = (time()-strtotime($input))/60;
        if ($diff < 60) $output = "about ".round($diff)." min ago";
        elseif ($diff < 1440) $output = "about ".round($diff/60)." hours ago";
        elseif ($diff < 7200) $output = round($diff/1400)." days ago at ".date("g:i a", strtotime($input));
        else $output = date("M j, Y \\a\\t g:i a", strtotime($input));

        return $output;
    }

    /**
     *
     * function: diff
     * Gets the difference between two dates in a specified timezone
     * @access public
     * @param DateTime $dt1
     * @param DateTime $dt2
     * @param string $timeZone (optional)
     * @return int
     */
    public static function diff($dt1,$dt2,$timeZone='GMT') {
        $tZone = new DateTimeZone($timeZone);

        $dt1 = new DateTime($dt1, $tZone);
        $ts1 = $dt1->format('Y-m-d');

        $dt2 = new DateTime($dt2, $tZone);
    	$ts2 = $dt2->format('Y-m-d');

    	$diff = (strtotime($ts1) - strtotime($ts2));
        $diff /= 3600 * 24;

        return $diff;
    }

    /**
     *
     * function: isPast
     * Determines if a date is in the past
     * @access public
     * @param DateTime $dt
     * @param string $timeZone (optional)
     * @return boolean
     */
    public function isPast($dt,$timeZone='GMT') {
        return DateData::diff($dt,date('Y-m-d H:i:s'),$timeZone) < 0;
    }

    /**
     *
     * function: isFuture
     * Determines if a date is in the future
     * @access public
     * @param DateTime $dt
     * @param string $timeZone (optional)
     * @return boolean
     */
    public function isFuture($dt,$timeZone='GMT') {
        return DateData::diff($dt,date('Y-m-d H:i:s'),$timeZone) > 0;
    }
}

