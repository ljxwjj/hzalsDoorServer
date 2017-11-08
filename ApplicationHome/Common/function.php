<?php
require_once 'common.php';

function isWorkDay($day) {
    $time = strtotime($day);
    $year = date("Y", $time);
    $mmdd = date("md", $time);
    $week = date("w", $time);

    if (is_array($GLOBALS["jiari_data"][$year])) {
        $jiari = $GLOBALS["jiari_data"][$year]["jiari"];
        $gongzuo = $GLOBALS["jiari_data"][$year]["gongzuo"];
    } else {
        $jiariFile = dirname(__FILE__)."/jiari/$year.txt";
        if (file_exists($jiariFile)) {
            $jiari = file_get_contents($jiariFile);
            $GLOBALS["jiari_data"][$year]["jiari"] = $jiari;
        } else {
            $jiari = file_get_contents("http://tool.bitefu.net/jiari/data/".$year.".txt");
            $GLOBALS["jiari_data"][$year]["jiari"] = $jiari;
            if (!empty($jiari)) file_put_contents($jiariFile, $jiari);
        }
        $gongzuoFile = dirname(__FILE__)."/jiari/".$year."_w.txt";
        if (file_exists($gongzuoFile)) {
            $gongzuo = file_get_contents($gongzuoFile);
            $GLOBALS["jiari_data"][$year]["gongzuo"] = $gongzuo;
        } else {
            $gongzuo = file_get_contents("http://tool.bitefu.net/jiari/data/".$year."_w.txt");
            $GLOBALS["jiari_data"][$year]["gongzuo"] = $gongzuo;
            if (!empty($gongzuo)) file_put_contents($gongzuoFile, $gongzuo);
        }
    }

    if (strpos($jiari, $mmdd) !== false) return false;
    if (strpos($gongzuo, $mmdd) !== false) return true;
    if ($week == "0" || $week == "6") return false;
    return true;
}

function dateToWeek($date) {
    $dateTime = strtotime($date);
    $week = date("w", $dateTime);
    switch ($week) {
        case "0":return "星期天";
        case "1":return "星期一";
        case "2":return "星期二";
        case "3":return "星期三";
        case "4":return "星期四";
        case "5":return "星期五";
        case "6":return "星期六";
    }
}

function hitotime($hi) {
    return strtotime($hi)%(24*60*60);
}

function toWorkHours($time) {
    $h = intval($time/60/60);
    $i = intval(($time%(60*60))/60);
    if ($h > 0) return $h."时".$i."分";
    else if ($i > 0) return $i."分";
    else return "";
}