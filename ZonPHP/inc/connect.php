<?php
ob_start();
mysqli_report(MYSQLI_REPORT_OFF);

$con = mysqli_connect($params['database']['host'], $params['database']['username'], $params['database']['password'], $params['database']['database']);

if (!$con) {
    die(header('location:pages/error.php?fout=connect'));
}

if ($params['useWeewx']) {
    $con_weewx = mysqli_connect($params['weewx']['server'], $params['weewx']['username'], $params['weewx']['password'], $params['weewx']['database']);

    if (!$con_weewx) {
        die(header('location:pages/error.php?fout=connect'));
    }
}
ob_end_flush();

// clear password, not to be exposed by accident
$params['database']['password'] = "undefined";

function checkOrCreateTables($con)
{
    /****************************************************************************
     * Create table _dag to store day values per inverter
     ****************************************************************************/
    $sql_createDayTable = "CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "_dag (
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
    $sql_createMonthTable = "CREATE TABLE IF NOT EXISTS " . TABLE_PREFIX . "_maand (
				  IndexMaand varchar(40) NOT NULL,
				  Datum_Maand datetime NOT NULL,
				  Geg_Maand float NOT NULL,
				  Naam varchar(21) NOT NULL,
				  UNIQUE KEY IndexMaand (IndexMaand)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1";


    $result = mysqli_query($con, "SHOW TABLES LIKE '" . TABLE_PREFIX . "_dag'");
    if ($result->num_rows != 1) {
        mysqli_query($con, $sql_createDayTable) or die("Query failed. sql_createDayTable: " . mysqli_error($con));
    }

    $result = mysqli_query($con, "SHOW TABLES LIKE '" . TABLE_PREFIX . "_maand'");
    if ($result->num_rows != 1) {
        mysqli_query($con, $sql_createMonthTable) or die("Query failed. sql_createMonthTable: " . mysqli_error($con));
    }

}