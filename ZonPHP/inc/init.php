<?php
global $zonPHPSessionID;
/**
 * Init set default values and constants for this installation
 * starts session and loads parameter, language and themes without database access
 * Try to get all values from session and load only when needed
 *
 */
include_once "version_info.php";
include_once "common.php";  // include common library functions to be used everywhere

$debugmode = false;
error_reporting(E_ALL);          // place these two lines at the top of
/// error_reporting (E_ALL & ~E_NOTICE & ~E_DEPRECATED);
ini_set('display_errors', 1);    // the script you are debugging

/*********************************************************************
 * start session change $zonPHPSessionID when needed
 *********************************************************************/

session_name($zonPHPSessionID);
session_start();

/*********************************************************************
 * define path's and installed languages
 *********************************************************************/

define('ROOT_DIR', realpath(substr(realpath(__DIR__ . '/'), 0, -4) . '/'));
$tmpHTMLPath = getHTMLPATH(ROOT_DIR, $_SERVER['DOCUMENT_ROOT']);

if (strlen($tmpHTMLPath) == 0) $tmpHTMLPath = "/";
if ($tmpHTMLPath[0] != "/") {
    $tmpHTMLPath = "/" . $tmpHTMLPath;
}
if ($tmpHTMLPath[strlen($tmpHTMLPath) - 1] != "/") {
    $tmpHTMLPath = $tmpHTMLPath . "/";
}
checkChangedConfigFiles();

/*********************************************************************
 * init params, language, themes, timezone
 *********************************************************************/
if (!isset($_SESSION['params']) || isset($_GET['params'])) {
    include_once "load_parameters.php";
    loadParams($tmpHTMLPath);
}

// use default HTML Path if not overwritten
if (!defined("HTML_PATH")) {
    define('HTML_PATH', $tmpHTMLPath);
}
$_SESSION['HTML_PATH'] = HTML_PATH;

$params = $_SESSION['params'];
if (!defined("TABLE_PREFIX")) {
    define('TABLE_PREFIX', $params['database']['tablePrefix']);
}
if (!defined("PLANT_NAMES")) {
    define('PLANT_NAMES', $_SESSION['PLANT_NAMES']);
}

// language
if (!isset($_SESSION['txt']) || isset($_GET['language'])) {
    include_once "load_language.php";
    loadLanguage($params);
}

// set default timezone
date_default_timezone_set($params['timeZone']);

// theme
if (!isset($_SESSION['colors']) || !isset($_SESSION['theme']) || isset($_GET['theme'])) {
    include_once "load_themes.php";
    loadTheame($params);
}
$colors = $_SESSION['colors'];

// set default plant
if (!isset($_SESSION['plant'])) {
    $_SESSION['plant'] = PLANT_NAMES[0];
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
$github_version = "unknown";
$new_version_label = "";
const CACHE_TIMEOUT = 86400;  // 24h currently only used for version check
const TIME_OFFSET = 7200;  // offest in seconds e.g. 2h

const NODATE = "2000-01-01";
const BIG_CHART_HIGHT = 500;
const HEADER_CLASS = 'jqx-window-header jqx-window-header-zonphp jqx-widget-header jqx-widget-header-zonphp jqx-disableselect jqx-disableselect-zonphp jqx-rc-t jqx-rc-t-zonphp';
const WINDOW_STYLE_CHART = 'padding: 0px; background-color: inherit; border: 2px; border-color: #000; margin: 0px 0px 0px 0px;border-width: 1px; border-style: solid; border-radius: 10px; width:100%; height:400px';
const WINDOW_STYLE = 'padding: 0px; border: 2px; border-color: #000; margin: 3px; border-width: 1px; border-style: solid; border-radius: 10px; color:#000000;';


if (isset($_SESSION['github_version'])) $github_version = $_SESSION['github_version'];
if (isset($_SESSION['new_version_label'])) $new_version_label = $_SESSION['new_version_label'];

date_default_timezone_set("UTC");
/** @noinspection PhpSwitchCanBeReplacedWithMatchExpressionInspection */
switch ($_SESSION['language']) {
    case "nl":
        $locale = 'nl-NL';
        break;
    case  "fr":
        $locale = 'fr-FR';
        break;
    case "de":
        $locale = 'de-DE';
        break;
    default:
        $locale = 'en-US';
}

// preparing a localized month array
$formatter = new IntlDateFormatter($locale, IntlDateFormatter::NONE,
    IntlDateFormatter::NONE, NULL, NULL, "MMMM");




