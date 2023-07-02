<?php

$debugmode = false;
error_reporting(E_ALL);          // place these two lines at the top of
/// error_reporting (E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);    // the script you are debugging

/*********************************************************************
 * start session change $zonPHPSessionID when needed
 *********************************************************************/
$zonPHPSessionID = "SOLAR4";
session_name($zonPHPSessionID);
session_start();

/*********************************************************************
 * define pathes
 *********************************************************************/

include_once "common.php";

define('ROOT_DIR', realpath(substr(realpath(__DIR__ . '/'), 0, -4) . '/'));
$tmpHTMLPath = str_replace('\\', '/', substr(ROOT_DIR, strlen($_SERVER['DOCUMENT_ROOT'])));
if (strlen($tmpHTMLPath) == 0) $tmpHTMLPath = "/";
if ($tmpHTMLPath[0] != "/") {
    $tmpHTMLPath = "/" . $tmpHTMLPath;
}
if ($tmpHTMLPath[strlen($tmpHTMLPath) - 1] != "/") {
    $tmpHTMLPath = $tmpHTMLPath . "/";
}
define('HTML_PATH', $tmpHTMLPath);
define('PHP_PATH', ltrim(HTML_PATH, '/'));

include_once ROOT_DIR . "/inc/version_info.php";

//echo "HTML_PATH: " .  HTML_PATH. "<br>";
//echo "ROOT_DIR: " .  ROOT_DIR. "<br>";
//echo "PHP_PATH: " .  PHP_PATH. "<br>";


/*********************************************************************
 * init params, language, themes
 *********************************************************************/
if (!isset($_SESSION['params']) || isset($_GET['params'])) {
    include_once "load_parameters.php";
} else {
    $params = $_SESSION['params'];
}
getConstants($params);

if (!isset($_SESSION['txt']) || isset($_GET['language'])) {
    include_once "load_language.php";
    // fixme: still needed?
    $txt = $_SESSION['txt'];
}
if (!isset($_SESSION['colors']) || isset($_GET['theme'])) {
    include_once "load_themes.php";
} else {
    $colors = $_SESSION['colors'];
}
// set default inverter
// FIXME: still needed?
if (isset($_GET['naam'])) {
    $_SESSION['plant'] = $_GET['naam'];
}
// set default plant
if (!isset($_SESSION['plant'])) {
    $_SESSION['plant'] = PLANTS[0];
}
if (!isset($_SESSION['date_minimum'])) {
    $_SESSION['date_minimum'] = strtotime('2138-01-01 00:00:00');
}
if (!isset($_SESSION['date_maximum'])) {
    $_SESSION['date_maximum'] = strtotime('1990-01-01 00:00:00');
}

/*********************************************************************
 * define defaults
 *********************************************************************/
$cache_timeout = 500;
$time_offset = 7200;  // offest in seconds e.g. 2h
$big_chart_height = 500;
//fixme: version check
$github_version = "unknown";
$new_version_label = "";
if (isset($_SESSION['github_version'])) $github_version = $_SESSION['github_version'];
if (isset($_SESSION['new_version_label'])) $new_version_label = $_SESSION['new_version_label'];
// fixme
$total_sum_for_all_years = 0;

if (isset($_SESSION['language'])) {
    $language = $_SESSION['language'];
} else {
    $language = $params['defaultLanguage'];
}

$locale = 'en-US';
date_default_timezone_set("UTC");

if ($language == "nl") {
    $locale = 'nl-NL'; // For IntlDateFormatter
}
if ($language == "fr") {
    $locale = 'fr-FR'; // For IntlDateFormatter
}
if ($language == "de") {
    $locale = 'de-DE'; // For IntlDateFormatter
}
if ($language == "en") {
    $locale = 'en-US'; // For IntlDateFormatter
}

// preparing a localized month array
$formatter = new IntlDateFormatter($locale, IntlDateFormatter::NONE,
    IntlDateFormatter::NONE, NULL, NULL, "MMMM");




