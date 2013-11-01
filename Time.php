<?php
class Tmonitor_Model_Time
{
    const SECM = 2592000;
    const SECH = 3600;
    const SECD = 86400;
    public static function compress($time = null, $secs = 1, $start = 0)
    {
        if ($time === null) {
            $time = time();
        } else if (!is_numeric($time)) {
            $time = strtotime($time);
        }
        
        if (!is_numeric($secs) || $secs < 1) {
            $secs = 1;
        }
        
        if (!is_numeric($start)) {
            $start = strtotime($start);
        }
        
        $time = $time - $start;
        
        if ($time > 0) {
            $time = round($time/$secs);
        } else {
            $time = 0;
        }
        
        return $time;
    }

    public static function getDuring($time, $start)
    {
        $during = self::compress($time, 1, $start);
        if ($during < 60) {            
            return $during."秒";
        } else if ($during >60 && $during < 3600) {
            $during = self::compress($time, 60, $start);
            return $during."分";
        } else if ($during > 3600 && $during < 24*3600) {
            $during = self::compress($time, 3600, $start);
            return $during."小时";
        } else {
            $during = self::compress($time, 24*3600, $start);
            return $during."天";
        }
    }
}