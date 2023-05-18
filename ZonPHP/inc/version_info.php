<?php

/********************************************************************
 * Version
 *********************************************************************/
$version = "v2023.05.01";

/********************************************************************
 * Debug
 *********************************************************************/
$debugmode = true;

error_reporting(E_ALL);          // place these two lines at the top of
/// error_reporting (E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);    // the script you are debugging


/*********************************************************************
 * sessionID  change only when needed
 *********************************************************************/
$zonPHPSessionID = "SOLAR";

$cache_timeout = 500;
$time_offset = 7200;  // offest in seconds e.g. 2h
$big_chart_height = 500;
?>

