<?php
/**
 common functions
 */
function getTxt($key)
{
    if (isset($_SESSION["txt"]) && isset($_SESSION["txt"][$key])) {
        return $_SESSION["txt"][$key];
    } else {
        return "undefined key: " . $key;
    }
}

function isActive($language)
{
    if (in_array(strtolower($language), $_SESSION['LANGUAGES'])) {
        return true;
    } else {
        return false;
    };
}

function getConstants($myParams){
    if (!defined("TABLE_PREFIX")) {
        define('TABLE_PREFIX', $myParams['database']['tablePrefix']);
    }
    if (!defined("STARTDATE")) {
        define('STARTDATE', $myParams['installationDate']);
    }
    if (!defined("PLANTS")) {
        define('PLANTS', $_SESSION['PLANTS']);
    }
}