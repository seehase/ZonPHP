<?php

//
// templogger writes data into seonsordate_temp table
// cronjob will summarize every 5 minuts data from temp table and inserts into sensordata table
// then delete data from temp
// reduce amount of data


function logger($data)
{
   error_log($data);
}


logger("------ start -----------------------------");
logger("------  processing");

$callparams = $_SERVER["QUERY_STRING"];

$args = explode("&", $callparams);
$nargs = count($args);

//-------------------------------------------------------------------------------

include "Parameters.php";
include "inc/connect.php";

if ($nargs != 1) {
    error_log("wrong parameter count: " . $nargs);
    die();
}

if (!isset($datalogger_password)) {
    error_log("no \$datalogger_password defined in Parameter.php ");
    die();
}

if ($args[0] != $datalogger_password) {
    error_log("wrong credentials call " . $callparams);
    die();
}

// $newtime = ($mtime + 946681200) + 3600;   // Date since 1.1.2000 + diff since 1.1.1970 (946681200) + 1h
// $newtimestring = strftime("%Y-%m-%d %H:%M:%S", $newtime);
// logger("new Date: $newtime -- > $newtimestring");

// remember start date
$now = time();

// get latest date from DB
$sql = "SELECT max(logtime) from " . $table_prefix. "_sensordata_temp";
$result = mysqli_query($con, $sql) or die("Query failed. totaal " . mysqli_error($con));
if (mysqli_num_rows($result) != 0) {
    while ($row = mysqli_fetch_array($result))
    {
        $latest_date = $row[0];
    }

    // fixme... 7200 ist der Offset

    $sql = " INSERT IGNORE INTO tgeg_sensordata (id, logtime, measurevalue, sensorid, sensortype)
                 select 
                   uuid() as id,
                   FROM_UNIXTIME(FLOOR(UNIX_TIMESTAMP(logtime)/ 900) * 900 - (0)) AS logtime1,
                   avg(measurevalue) as measurevalue, 
                   sensorid, 
                   sensortype
                from 
                   tgeg_sensordata_temp
                where                   
                   logtime < '$latest_date'
                group by 
                   logtime1, sensorid, sensortype
            ";

    logger($sql);
    $result = mysqli_query($con, $sql) or die ('SQL Error string:' . mysqli_error($con));

    // delete from temp-table
    $sql = "delete from " . $table_prefix . "_sensordata_temp 
            where logtime < '" .$latest_date . "'";

    logger($sql);
    $result = mysqli_query($con, $sql) or die ('SQL Error string:' . mysqli_error($con));
}

logger("====== end ===============================");
echo "OK"
?>
