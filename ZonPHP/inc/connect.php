<?php
global $params, $debugmode, $version, $github_version, $new_version_label;
/********************************************************************
 * connect to database, refresh cache and import data
 *********************************************************************/
ob_start();
mysqli_report(MYSQLI_REPORT_OFF);

$con = mysqli_connect($params['database']['host'], $params['database']['username'], $params['database']['password'], $params['database']['database']);

if (!$con) {
    addCheckMessage("ERROR", "Cannot connect to database, check database section in parameter.php", true);
    header('location:' . HTML_PATH . '/pages/validate.php');
    die();
} else {
    checkOrCreateTables($con);
}

if ($params['useWeewx']) {
    $con_weewx = mysqli_connect($params['weewx']['host'], $params['weewx']['username'], $params['weewx']['password'], $params['weewx']['database']);

    if (!$con_weewx) {
        addCheckMessage("ERROR", "Cannot connect to WEEWX database, check weewx section in parameter.php disabled weewx for now");
        // continue without weewx
        $params['useWeewx'] = false;
        $_SESSION['params'] = $params;
        //die(header('location:' . HTML_PATH . '/pages/validate.php'));
    } else {
        checkWeewxTables($con_weewx);
    }
}
ob_end_flush();

// clear password, not to be exposed by accident
$params['database']['password'] = "undefined";

/********************************************************************
 * internal caching (currently only for version check
 *********************************************************************/
if (isset($_SESSION['lastupdate']) && ($_SESSION['lastupdate'] + CACHE_TIMEOUT) > (time())) {
    // cache still valid --> do not reload cache
    if ($debugmode) error_log("cache hit --> ");
} else {
    // reload cache
    if ($debugmode) error_log("cache failed --> need to reload data");

    // get latest Version from github can cause error on some provider e.g.bplaced do not allow file_get_content
    if ($params['checkVersion']) {
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
    }
    $_SESSION['github_version'] = $github_version;

    if ($github_version > $version) {
        $new_version_label = " - new version " . $github_version;
    }
    $_SESSION['new_version_label'] = $new_version_label;
    $_SESSION['lastupdate'] = time();
}
/********************************************************************
 * import data
 *********************************************************************/
// import new data
include_once ROOT_DIR . "/inc/import_data.php";

/********************************************************************
 * helper --> could be moved
 *********************************************************************/
function checkOrCreateTables($con): void
{
    /****************************************************************************
     * Create table _dag to store day values per inverter
     ****************************************************************************/
    $tablename_dag = TABLE_PREFIX . "_dag";
    $sql_createDayTable = "CREATE TABLE IF NOT EXISTS $tablename_dag (
				  IndexDag varchar(40) NOT NULL,
				  Datum_Dag datetime NOT NULL,
				  Geg_Dag float NOT NULL,
				  kWh_Dag float NOT NULL,
				  Naam varchar(21) NOT NULL,
				  UNIQUE KEY IndexDag (IndexDag)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1";

    /****************************************************************************
     * Create table _maand to store aggregated monthly values per inverter
     ****************************************************************************/
    $tablename_maand = TABLE_PREFIX . "_maand";
    $sql_createMonthTable = "CREATE TABLE IF NOT EXISTS $tablename_maand (
				  IndexMaand varchar(40) NOT NULL,
				  Datum_Maand datetime NOT NULL,
				  Geg_Maand float NOT NULL,
				  Naam varchar(21) NOT NULL,
				  UNIQUE KEY IndexMaand (IndexMaand)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1";


    $result = mysqli_query($con, "SHOW TABLES LIKE '$tablename_dag'");
    if ($result->num_rows != 1) {
        if (!mysqli_query($con, $sql_createDayTable)) {
            addCheckMessage("ERROR", "Unable to create table'$tablename_dag'", true);
            header('location:' . ROOT_DIR . '/pages/validate.php' . mysqli_error($con));
            die();
        }
    }

    $result = mysqli_query($con, "SHOW TABLES LIKE '$tablename_maand'");
    if ($result->num_rows != 1) {
        if (!mysqli_query($con, $sql_createMonthTable)) {
            addCheckMessage("ERROR", "Unable to create table'$tablename_maand'", true);
            header('location:' . ROOT_DIR . '/pages/validate.php' . mysqli_error($con));
            die();
        }
    }
}

function checkWeewxTables($con_weewx): void
{
    global $params;

    $tablename = $params['weewx']['tableName'];
    $result = mysqli_query($con_weewx, "SHOW TABLES LIKE '$tablename'");
    if ($result->num_rows != 1) {
        addCheckMessage("WARN", "Weewx table '$tablename' not found, disabled weewx for now", true);
        $params['useWeewx'] = false;
        $_SESSION['params'] = $params;
    }
}