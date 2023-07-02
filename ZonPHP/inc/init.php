<?php
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
const LANGUAGES = array("en", "de", "fr", "nl");
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
checkChangedConfigFiles();




//echo "HTML_PATH: " .  HTML_PATH. "<br>";
//echo "ROOT_DIR: " .  ROOT_DIR. "<br>";
//echo "PHP_PATH: " .  PHP_PATH. "<br>";


/*********************************************************************
 * init params, language, themes
 *********************************************************************/
if (!isset($_SESSION['params']) || isset($_GET['params'])) {
    include_once "load_parameters.php";
    loadParams();
}
$params = $_SESSION['params'];
if (!defined("TABLE_PREFIX")) {
    define('TABLE_PREFIX', $params['database']['tablePrefix']);
}
if (!defined("STARTDATE")) {
    define('STARTDATE', $params['installationDate']);
}
if (!defined("PLANTS")) {
    define('PLANTS', $_SESSION['PLANTS']);
}

// language
if (!isset($_SESSION['txt']) || isset($_GET['language'])) {
    include_once "load_language.php";
    loadLanguage($params);
}
// theme
if (!isset($_SESSION['colors']) || !isset($_SESSION['theme']) || isset($_GET['theme'])) {
    include_once "load_themes.php";
    loadTheame($params);
}
$colors = $_SESSION['colors'];

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

date_default_timezone_set("UTC");
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




