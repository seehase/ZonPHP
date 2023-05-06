<?php
/*
 * load  only if changed or invalid cache in session
 */
include_once "connect.php";

// set default inverter
if (isset($_GET['naam']))
    $_SESSION['Wie'] = $_GET['naam'];

// get theme
if (isset($_GET['theme'])) {
    include_once "load_colors.php";
}

if ($taal == "nl")
    setlocale(LC_TIME, 'Dutch_Netherlands', 'Dutch', 'nl_NL', 'nl', 'nl_NL.ISO8859-1', 'nl_NL.UTF-8', 'nld_nld', 'nld', 'nld_NLD', 'NL_nl');

if ($taal == "fr")
    setlocale(LC_TIME, 'fr', 'fr_FR', 'fr_FR.ISO8859-1', 'fr_FR.UTF8', 'fr.UTF8', 'fr_FR.UTF-8', 'fr.UTF-8');

if ($taal == "de") {
    setlocale(LC_TIME, 'de', 'de_DE.utf8', 'de_DE', 'deutsch', 'german');
    // setlocale(LC_TIME, 'German', 'de_DE', 'deu', 'de_DE', 'de');
}
if ($taal == "en")
    setlocale(LC_TIME, 'english-us', 'English', 'en_US', 'en', 'en_US.ISO8859-1', 'en_US.UTF-8', 'en');

date_default_timezone_set("UTC");



$github_version = "unknown";
$new_version_label = "";

// fixme
$total_sum_for_all_years = 0;
$editLayout = false;
if (isset($_SESSION['editLayout'])) $editLayout = $_SESSION['editLayout'];


if (isset($_SESSION['lastupdate']) && ($_SESSION['lastupdate'] + $cache_timeout) > (time())) {
    // cache still valid --> do not reload cache
    if ($debugmode) error_log("cache hit --> ");
    // copy data from session into variabls

//     $total_sum_for_all_years = $_SESSION['total_sum_for_all_years'];   //jhs in use last years and import
//    $max_month = $_SESSION['max_month'];  // jhs in use last year



    $txt = $_SESSION['txt'];
    $year_euro = $_SESSION['year_euro'];
    $price_per_kwh = $year_euro[date("Y")];

    $months = $_SESSION['months'];

    if (!isset($_SESSION['param'])) {
        include_once "load_parameters.php";
    } else {
        $param = $_SESSION['param'];
    }

    $charts = $_SESSION['charts'];
    if (!isset($charts['chart_date_format'])) {
        $charts['chart_date_format'] = "";
    }

    if (!isset($_SESSION['colors'])) {
        include_once "load_colors.php";
    } else {
        $colors = $_SESSION['colors'];
    }
    $sNaamSaveDatabase = $_SESSION['sNaamSaveDatabase'];
    $dstartdatum = $_SESSION['dstartdatum'];
    $iveromvormers = $_SESSION['iveromvormers'];
    $ieffectief_kwpiek = $_SESSION['ieffectief_kwpiek'];
    $ieffectiefkwpiek = $_SESSION['ieffectiefkwpiek'];
    $colors = $_SESSION['colors'];

    $date_minimum = $_SESSION['date_minimum'];
    $date_maximum = $_SESSION['date_maximum'];

    if (isset($_SESSION['github_version']))  $github_version = $_SESSION['github_version'];
    if (isset($_SESSION['new_version_label'])) $new_version_label = $_SESSION['new_version_label'];

    // error_log("cache is valid  " . ($_SESSION['lastupdate'] + $cache_timeout) . " - " . time());
} else {
    // reload cache
    // error_log("+++++++++++cache is NOT valid");
    if ($debugmode) error_log("cache failed --> need to reload data");

    // load lanaguages -----------------------------------------------------------------------------------------------
    include "inc/language/en.php";
    if (isset($_SESSION['sestaal'])) {
        include "inc/language/" . $_SESSION['sestaal'] . ".php";
    }
    $_SESSION['txt'] = $txt;


    // load parameters from DB
    include_once "load_parameters.php";

    // load color and theme
    include_once "load_colors.php";


    // load euro -------------------------------------------------------------------------------------------------------
    $sqleuro = "SELECT DATE_FORMAT(Datum_Euro,'%Y') as year, Geg_Euro as euro_val
	FROM " . $table_prefix . "_euro";
    $resulteuro = mysqli_query($con, $sqleuro) or die("Query failed. jaar-euro " . mysqli_error($con));
    if (mysqli_num_rows($resulteuro) == 0) {
        $year_euro = array();
    } else {
        while ($row = mysqli_fetch_array($resulteuro)) {
            $year_euro[$row['year']] = $row['euro_val'];
        }
    }
    $_SESSION['year_euro'] = $year_euro;
    $price_per_kwh = $year_euro[date("Y")];

    // fixme: integrate into cache... after importing data force reload of paramater
    // load first and last date of date
    $sqlminmax = "SELECT   
                    DATE_FORMAT(MAX(Datum_Dag), '%Y-%m-%d') AS maxi,
                    DATE_FORMAT(MIN(Datum_Dag), '%Y-%m-%d') AS mini
               FROM " . $table_prefix . "_dag
               WHERE Naam='" . $_SESSION['Wie'] . "'";
    $resultminmax = mysqli_query($con, $sqlminmax) or die("Query failed. dag-minmax " . mysqli_error($con));

    $date_minimum = strtotime('2038-01-01 00:00:00');
    $date_maximum = strtotime('1990-01-01 00:00:00');
    $_SESSION['date_minimum'] = $date_minimum;
    $_SESSION['date_maximum'] = $date_maximum;
    $values_found = true;
    while ($row = mysqli_fetch_array($resultminmax)) {
        if ($row['mini'] == null){
            $values_found = false;
        }
        $date_minimum = strtotime($row['mini']);
        $date_maximum = strtotime($row['maxi']);
        $_SESSION['date_minimum'] = $date_minimum;
        $_SESSION['date_maximum'] = $date_maximum;
    }
    // fallback if only data in maand table
    if (!$values_found){
        $sqlminmax = "SELECT   
                    DATE_FORMAT(MAX(Datum_maand), '%Y-%m-%d') AS maxi,
                    DATE_FORMAT(MIN(Datum_maand), '%Y-%m-%d') AS mini
               FROM " . $table_prefix . "_maand
               WHERE Naam='" . $_SESSION['Wie'] . "'";
        $resultminmax = mysqli_query($con, $sqlminmax) or die("Query failed. maand-minmax " . mysqli_error($con));
        while ($row = mysqli_fetch_array($resultminmax)) {
            $date_minimum = strtotime($row['mini']);
            $date_maximum = strtotime($row['maxi']);
            $_SESSION['date_minimum'] = $date_minimum;
            $_SESSION['date_maximum'] = $date_maximum;
        }
    }

    // get latest Version from github
    $github_version = "unkown";
    $homepage = "";
    if (strpos($version, "(dev)") > 0)    {
        $homepage = file_get_contents('https://raw.githubusercontent.com/seehase/ZonPHP/development/ZonPHP/inc/version_info.php');
    }
    else    {
        $homepage = file_get_contents('https://raw.githubusercontent.com/seehase/ZonPHP/master/ZonPHP/inc/version_info.php');
    }
    $pos_start = strpos($homepage, '"v');
    $pos_end = strpos($homepage, '";', $pos_start + 2);

    if ($pos_start > 0) {
        $github_version = substr($homepage, $pos_start+1, $pos_end-$pos_start-1 );
    }
    $_SESSION['github_version'] = $github_version;

    $new_version_label = "";
    if ($github_version > $version) {
        $new_version_label = "new version available!!!! -> " . $github_version;
    }
    $_SESSION['new_version_label'] = $new_version_label;

    // -----------------------------------------------------------------------------------------------------------------
    $_SESSION['lastupdate'] = time();
}


?>