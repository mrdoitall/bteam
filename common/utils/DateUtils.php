<?php


namespace common\utils;


class DateUtils
{
    static function getUnixFromDateFormat($value, $format = '%d/%m/%Y', $startDate = true, $timeZone = 'Asia/Ho_Chi_Minh')
    {
        if ($timeZone !== null) {
            date_default_timezone_set($timeZone);
        }
        $a = strptime($value, $format);
        if ($startDate) {
            return mktime(0, 0, 0, $a['tm_mon'] + 1, $a['tm_mday'], $a['tm_year'] + 1900);
        } else {
            return mktime(23, 59, 59, $a['tm_mon'] + 1, $a['tm_mday'], $a['tm_year'] + 1900);
        }
    }

    static function displayFromUnix($value, $format = 'd/m/Y', $timeZone = 'Asia/Ho_Chi_Minh')
    {
        if ($timeZone !== null) {
            date_default_timezone_set($timeZone);
        }
        return gmdate($format, $value);
    }

    static function getUnixFromDateTimeFormat($value, $format = '%d/%m/%Y %H:%M:%S', $timeZone = 'Asia/Ho_Chi_Minh')
    {
        if ($timeZone !== null) {
            date_default_timezone_set($timeZone);
        }
        $a = strptime($value, $format);
        return mktime($a['tm_hour'], $a['tm_min'], $a['tm_sec'], $a['tm_mon'] + 1, $a['tm_mday'], $a['tm_year'] + 1900);
    }
}
