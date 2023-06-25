<?php
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

//echo "HTML_PATH: " .  HTML_PATH. "<br>";
//echo "ROOT_DIR: " .  ROOT_DIR. "<br>";
//echo "PHP_PATH: " .  PHP_PATH. "<br>";

$params = parse_ini_file(ROOT_DIR . "/parameters.php", true);
vadidateParams($params);

// FIXME: enhance validation
/**
 *
 * Check parameter, and set defaults.
 * @param $params
 * @return void
 */
function vadidateParams(&$params)
{
    $params['plant']['importer'] = $params['importer'];
    $params['plant']['installationDate'] = $params['installationDate'];
    define('TABLE_PREFIX', $params['database']['tablePrefix']);
    define('STARTDATE', $params['installationDate']);
    $plants = preg_split('/\s*,\s*/', trim($params['plants']));
    define('PLANTS', $plants);

    $plants_kWp = json_decode('[' . $params['plantskWp'] . ']', true);
    define('PLANTS_kWp', $plants_kWp);
    $totalExpectedYield = 0.0;
    $expectedYield = array();
    $totalExpectedMonth = array();
    foreach (PLANTS as $plant) {
        $totalExpectedMonth[0][$plant] = 0;
        $values = json_decode('[' . $params[$plant]['expectedYield'] . ']', true);
        $params[$plant]['referenceYield'] = $values;
        $totalSum = array_sum($values);
        foreach ($values as $id => $value) {
            $totalExpectedMonth[$id+1][$plant] = $value;
        }
        $params[$plant]['totalExpectedYield'] = $totalSum;
        $totalExpectedYield += $totalSum;
        $expectedYield[] = $totalSum;
    }
    $params['totalExpectedMonth'] = $totalExpectedMonth;

    $params['totalExpectedYield'] = $totalExpectedYield;
    $params['expectedYield'] = $expectedYield;

    $languages = preg_split('/\s*,\s*/', trim($params['supportedLanguages']));
    $languages = array_map('strtolower', $languages);
    define('LANGUAGES', $languages);
    define('CHART_DATE_FORMAT', array("chart_date_format" => ""));

}


/********************************************************************
 * Version
 *********************************************************************/
$version = "v2023.07.24";

/********************************************************************
 * Debug
 *********************************************************************/
$debugmode = false;

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

