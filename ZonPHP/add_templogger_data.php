<?php

function logger($data)
{
//   error_log($data);
}

$callparams = $_SERVER["QUERY_STRING"];

$args = explode("&", $callparams);
$nargs = count($args);

logger("------ start -----------------------------");
logger($callparams);
logger("------  processing");

//-------------------------------------------------------------------------------

include "Parameters.php";
include "inc/connect.php";

if ($nargs < 6) {
    error_log("wrong parameter count: " . $nargs);
    die();
}

if (!isset($datalogger_password)) {
    error_log("no \$datalogger_password defined in Parameter.php " );
    die();
}

if ($args[0] != $datalogger_password) {
    error_log("wrong credentials call " . $callparams);
    die();
}
$date = urldecode($args[1]);
$time = urldecode($args[2]);
$device = urldecode($args[3]);
$sensortype = urldecode($args[4]);
$value = urldecode($args[5]);
// $mtime = urldecode($args[6]);  // not used  same as date and time


$date = str_replace("'", " ", $date);
$time = str_replace("'", " ", $time);
$value = str_replace("'", " ", $value);

$datetime = $date . " " . $time;
$mtime = strtotime($datetime);  // used to create ID

if (isset($datalogger_offset))
{
    logger (" -- offest is set -------------------------------");
    logger ("added offset: " . $datalogger_offset) + " hour";
    logger("Datetime original  : " . $datetime);
    logger("timestamp original : " . $mtime);
    $mtime = strtotime("$datalogger_offset hour", $mtime);
    $datetime = strftime("%Y-%m-%d %H:%M:%S", $mtime);
    logger("Datetime new : " . $datetime);
    logger("timestamp new: " . $mtime);
    logger (" -- offest is set -------------------------------");
}

$id = $device . "-" . $sensortype . "-" . $mtime;

logger("Date      : " . $date);
logger("Time      : " . $time);
logger("Device    : " . $device);
logger("Type      : " . $sensortype);
logger("Value     : " . $value);
logger("Datetime  : " . $datetime);
logger("timestamp : " . $mtime);
logger("id        : " . $id);
logger("--------------------");

// $newtime = ($mtime + 946681200) + 3600;   // Date since 1.1.2000 + diff since 1.1.1970 (946681200) + 1h
// $newtimestring = strftime("%Y-%m-%d %H:%M:%S", $newtime);
// logger("new Date: $newtime -- > $newtimestring");


// save data to db
$sql = "insert into " . $table_prefix . "_sensordata (id, logtime, measurevalue, sensorid, sensortype)
VALUES ('$id', '$datetime',  $value, '$device', '$sensortype' )";

logger($sql);
$result = mysqli_query($con, $sql) or die ('SQL Error string:' . mysqli_error($con));
logger("====== end ===============================");
?>