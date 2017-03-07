<?php
ob_start();
$con = mysqli_connect($sserver, $susername, $spassword, $sdatabase_name);

if (!$con) {
    die(header('location:install/opstart_installatie.php?fout=connect'));
}

if ($use_weewx == true)
{
    $con_weewx = mysqli_connect($weewx_server, $weewx_username, $weewx_password, $weewx_database_name);
    
     if (!$use_weewx) {
        die(header('location:install/opstart_installatie.php?fout=connect'));
    }
}

ob_end_flush();
?>
