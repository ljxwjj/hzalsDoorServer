<?php
require_once 'common.php';

function getHttpRooDir() {
    $url = $_SERVER["REQUEST_SCHEME"].'://'.$_SERVER['HTTP_HOST'];//$_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    $port = $_SERVER["SERVER_PORT"];
    if ($port != '80') {
        $url .= ':'.$port;
    }
    $url .= __ROOT__;
    return $url;
}