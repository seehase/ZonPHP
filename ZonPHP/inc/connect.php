<?php
ob_start();
mysqli_report(MYSQLI_REPORT_OFF);

$con = mysqli_connect($params['database']['host'], $params['database']['username'], $params['database']['password'], $params['database']['database']);

if (!$con) {
    die(header('location:install/opstart_installatie.php?fout=connect'));
}

if ($params['useWeewx'] ) {
    $con_weewx = mysqli_connect($params['weewx']['server'], $params['weewx']['username'], $params['weewx']['password'], $params['weewx']['database']);

    if (!$con_weewx) {
        die(header('location:install/opstart_installatie.php?fout=connect'));
    }
}
ob_end_flush();