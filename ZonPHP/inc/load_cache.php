<?php
/*
 * load  only if changed or invalid cache in session
 */
include_once "connect.php";

// set default inverter
if (isset($_GET['naam'])) {
    $_SESSION['plant'] = $_GET['naam'];
}
// get theme
if (isset($_GET['theme'])) {
    include_once "load_themes.php";
}

// set default plant
if (!isset($_SESSION['plant']))
    $_SESSION['plant'] = PLANTS[0];

$github_version = "unknown";
$new_version_label = "";

// fixme
$total_sum_for_all_years = 0;

if (isset($_SESSION['lastupdate']) && ($_SESSION['lastupdate'] + $cache_timeout) > (time())) {
    // cache still valid --> do not reload cache
    if ($debugmode) error_log("cache hit --> ");
    // copy data from session into variabls

    $txt = $_SESSION['txt'];

    if (!isset($_SESSION['colors'])) {
        include_once "load_themes.php";
    } else {
        $colors = $_SESSION['colors'];
    }

    $colors = $_SESSION['colors'];
    $date_minimum = $_SESSION['date_minimum'];
    $date_maximum = $_SESSION['date_maximum'];

    if (isset($_SESSION['github_version'])) $github_version = $_SESSION['github_version'];
    if (isset($_SESSION['new_version_label'])) $new_version_label = $_SESSION['new_version_label'];

    // error_log("cache is valid  " . ($_SESSION['lastupdate'] + $cache_timeout) . " - " . time());
} else {
    // reset login status
    unset($_SESSION['passok']);
    // reload cache
    if ($debugmode) error_log("cache failed --> need to reload data");

    // load lanaguages -----------------------------------------------------------------------------------------------
    $txt = parse_ini_file(ROOT_DIR . "/inc/language/en.ini", false);
    if (isset($_SESSION['language'])) {
        $txt = parse_ini_file(ROOT_DIR . "/inc/language/" . $language . ".ini", false);
    }
    $_SESSION['txt'] = $txt;

    // load color and theme
    include_once ROOT_DIR . "/inc/load_themes.php";

    // fixme: integrate into cache... after importing data force reload of paramater
    // load first and last date of date
    $sqlminmax = "SELECT   
                    DATE_FORMAT(MAX(Datum_Dag), '%Y-%m-%d') AS maxi,
                    DATE_FORMAT(MIN(Datum_Dag), '%Y-%m-%d') AS mini
               FROM " . TABLE_PREFIX . "_dag
               WHERE Naam='" . $_SESSION['plant'] . "'";
    $resultminmax = mysqli_query($con, $sqlminmax) or die("Query failed. dag-minmax " . mysqli_error($con));

    $date_minimum = strtotime('2138-01-01 00:00:00');
    $date_maximum = strtotime('1990-01-01 00:00:00');
    $_SESSION['date_minimum'] = $date_minimum;
    $_SESSION['date_maximum'] = $date_maximum;
    $values_found = true;
    while ($row = mysqli_fetch_array($resultminmax)) {
        if ($row['mini'] == null) {
            $values_found = false;
        } else {
            $date_minimum = strtotime($row['mini']);
            $date_maximum = strtotime($row['maxi']);
            $_SESSION['date_minimum'] = $date_minimum;
            $_SESSION['date_maximum'] = $date_maximum;
        }
    }
    // fallback if only data in maand table
    if (!$values_found) {
        $sqlminmax = "SELECT   
                    DATE_FORMAT(MAX(Datum_maand), '%Y-%m-%d') AS maxi,
                    DATE_FORMAT(MIN(Datum_maand), '%Y-%m-%d') AS mini
               FROM " . TABLE_PREFIX . "_maand
               WHERE Naam='" . $_SESSION['plant'] . "'";
        $resultminmax = mysqli_query($con, $sqlminmax) or die("Query failed. maand-minmax " . mysqli_error($con));
        while ($row = mysqli_fetch_array($resultminmax)) {
            if ($row['mini'] != null) {
                $date_minimum = strtotime($row['mini']);
                $date_maximum = strtotime($row['maxi']);
                $_SESSION['date_minimum'] = $date_minimum;
                $_SESSION['date_maximum'] = $date_maximum;
            }
        }
    }

    // get latest Version from github can cause error on some provider e.g.bplaced do not allow file_get_content
    $github_version = "unknown";
    $homepage = "";
    if (!isset($version) || strlen($version) < 6) {
        $version = "unknown";
    }
    if ($params['checkVersion'] == true) {
        try {
            if (strpos($version, "(dev)") > 0) {
                $homepage = file_get_contents('https://raw.githubusercontent.com/seehase/ZonPHP/development/ZonPHP/inc/version_info.php');
            } else {
                $homepage = file_get_contents('https://raw.githubusercontent.com/seehase/ZonPHP/master/ZonPHP/inc/version_info.php');
            }
            $pos_start = strpos($homepage, '"v');
            $pos_end = strpos($homepage, '";', $pos_start + 2);

            if ($pos_start > 0) {
                $github_version = substr($homepage, $pos_start + 1, $pos_end - $pos_start - 1);
            }
        } catch (Throwable $e) {

        }
    }
    $_SESSION['github_version'] = $github_version;

    $new_version_label = "";
    if ($github_version > $version) {
        $new_version_label = " - new version " . $github_version;
    }
    $_SESSION['new_version_label'] = $new_version_label;

    // -----------------------------------------------------------------------------------------------------------------
    $_SESSION['lastupdate'] = time();
}
?>
