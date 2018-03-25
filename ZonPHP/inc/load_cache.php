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


if (isset($_SESSION['lastupdate']) && ($_SESSION['lastupdate'] + $cache_timeout) > (time())) {
    // cache still valid --> do not reload cache
    if ($debugmode) error_log("cache hit --> ");
    // copy data from session into variabls
    $avarage_per_month = $_SESSION['avarage_per_month'];
    $sum_per_year = $_SESSION['sum_per_year'];
    $total_sum_for_all_years = $_SESSION['total_sum_for_all_years'];
    $max_month = $_SESSION['max_month'];
    $missing_days_month_year = $_SESSION['missing_days_month_year'];

    $all_inverters_avarage_per_month = $_SESSION['all_inverters_avarage_per_month'];
    $all_inverters_sum_per_year = $_SESSION['all_inverters_sum_per_year'];
    $all_inverters_total_sum_for_all_years = $_SESSION['all_inverters_total_sum_for_all_years'];
    $all_inverters_max_month = $_SESSION['all_inverters_max_month'];
    $all_inverters_missing_days_month_year = $_SESSION['all_inverters_missing_days_month_year'];

    $txt = $_SESSION['txt'];
    $year_euro = $_SESSION['year_euro'];
    $short_weekdays = $_SESSION['short_weekdays'];
    $weekdays = $_SESSION['weekdays'];

    $months = $_SESSION['months'];

    if (!isset($_SESSION['param'])) {
        include_once "load_parameters.php";
    } else {
        $param = $_SESSION['param'];
    }

    $charts = $_SESSION['charts'];

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

    // load sum per month for all years --------------------------------------------------------------------------------
    $sql = "SELECT SUM( Geg_Maand ) AS sum_month, year( Datum_Maand ) AS year, month( Datum_Maand ) AS month,
            count( Datum_Maand ) AS tdag_maand
        FROM " . $table_prefix . "_maand
        WHERE Naam = '" . $_SESSION['Wie'] . "'
        GROUP BY year, month";

    $result = mysqli_query($con, $sql) or die("Query failed. totaal " . mysqli_error($con));
    $sum_per_year = array();
    $total_sum_for_all_years = 0;
    $avarage_per_month = 0;
    $max_month = 0;
    $missing_days_month_year = array();
    if (mysqli_num_rows($result) == 0) {
        $sum_per_year[date('Y-m-d', time())] = 0;
    } else {
        while ($row = mysqli_fetch_array($result)) {
            if (!isset($sum_per_year[$row['year']])) {
                $sum_per_year[$row['year']] = 0;
            }
            $sum_per_year[$row['year']] += $row['sum_month'];

            $days_per_month = cal_days_in_month(CAL_GREGORIAN, $row['month'], $row['year']);
            $missingdays = $days_per_month - $row['tdag_maand'];

            $missing_days_month_year[$row['year']][$row['month']] = $missingdays;
        }
        $avarage_per_month = array_sum($sum_per_year) / count($sum_per_year);
        $total_sum_for_all_years = array_sum($sum_per_year);
        $max_month = max($sum_per_year);
    }

    $_SESSION['avarage_per_month'] = $avarage_per_month;
    $_SESSION['sum_per_year'] = $sum_per_year;
    $_SESSION['total_sum_for_all_years'] = $total_sum_for_all_years;
    $_SESSION['max_month'] = $max_month;
    $_SESSION['missing_days_month_year'] = $missing_days_month_year;

    // load sum per month for all years and all interters ----------------------------------------------------------------------------
    $sql = "SELECT SUM( Geg_Maand ) AS sum_month, year( Datum_Maand ) AS year, month( Datum_Maand ) AS month,
            count( Datum_Maand ) AS tdag_maand
        FROM " . $table_prefix . "_maand        
        GROUP BY year, month";

    $result = mysqli_query($con, $sql) or die("Query failed. totaal " . mysqli_error($con));
    $all_inverters_sum_per_year = array();
    $all_inverters_total_sum_for_all_years = 0;
    $all_inverters_avarage_per_month = 0;
    $all_inverters_max_month = 0;
    $all_inverters_missing_days_month_year = array();
    if (mysqli_num_rows($result) == 0) {
        $all_inverters_sum_per_year[date('Y-m-d', time())] = 0;
    } else {
        while ($row = mysqli_fetch_array($result)) {
            if (!isset($all_inverters_sum_per_year[$row['year']])) {
                $all_inverters_sum_per_year[$row['year']] = 0;
            }
            $all_inverters_sum_per_year[$row['year']] += $row['sum_month'];

            $all_inverters_days_per_month = cal_days_in_month(CAL_GREGORIAN, $row['month'], $row['year']);
            $all_inverters_missingdays = $all_inverters_days_per_month - $row['tdag_maand'];

            $all_inverters_missing_days_month_year[$row['year']][$row['month']] = $all_inverters_missingdays;
        }
        $all_inverters_avarage_per_month = array_sum($all_inverters_sum_per_year) / count($all_inverters_sum_per_year);
        $all_inverters_total_sum_for_all_years = array_sum($all_inverters_sum_per_year);
        $all_inverters_max_month = max($all_inverters_sum_per_year);
    }

    $_SESSION['all_inverters_avarage_per_month'] = $all_inverters_avarage_per_month;
    $_SESSION['all_inverters_sum_per_year'] = $all_inverters_sum_per_year;
    $_SESSION['all_inverters_total_sum_for_all_years'] = $all_inverters_total_sum_for_all_years;
    $_SESSION['all_inverters_max_month'] = $all_inverters_max_month;
    $_SESSION['all_inverters_missing_days_month_year'] = $all_inverters_missing_days_month_year;


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


    // -----------------------------------------------------------------------------------------------------------------
    $_SESSION['lastupdate'] = time();
}


?>