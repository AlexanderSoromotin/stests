<?php

function checkCORS () {
    
    $url_arr = '';
    $reffer_url = '';
    if (isset($_SERVER['HTTP_REFERER'])) {
        $url_arr = parse_url($_SERVER['HTTP_REFERER']);
        $reffer_url = $url_arr['scheme'] . '://' . $url_arr['host'];
    }

    if (isset($url_arr['port'])) {
        $reffer_url .= ':' . $url_arr['port'];
    }

    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS' ){
        header('Access-Control-Allow-Origin: ' . $reffer_url);
        header('Access-Control-Allow-Methods: PROPFIND, PROPPATCH, COPY, MOVE, DELETE, MKCOL, LOCK, UNLOCK, PUT, GETLIB, VERSION-CONTROL, CHECKIN, CHECKOUT, UNCHECKOUT, REPORT, UPDATE, CANCELUPLOAD, HEAD, OPTIONS, GET, POST');
        header('Access-Control-Allow-Headers: Overwrite, Destination, Content-Type, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control');
        header("Access-Control-Max-Age: 86400");
        header('Access-Control-Allow-Credentials: true');
        exit;
    }

    /* СЕРВВЕРНЫЙ СКРИПТ ДЛЯ ИЗУЧЕНИЯ ReactApp */
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: ' . $reffer_url);
    header('Access-Control-Allow-Methods: PROPFIND, PROPPATCH, COPY, MOVE, DELETE, MKCOL, LOCK, UNLOCK, PUT, GETLIB, VERSION-CONTROL, CHECKIN, CHECKOUT, UNCHECKOUT, REPORT, UPDATE, CANCELUPLOAD, HEAD, OPTIONS, GET, POST');
    header('Access-Control-Allow-Headers: Overwrite, Destination, Content-Type, Depth, User-Agent, X-File-Size, X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control');
    header("Access-Control-Max-Age: 86400");
    header('Access-Control-Allow-Credentials: true');
}

checkCORS();

?>